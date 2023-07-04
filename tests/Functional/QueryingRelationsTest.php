<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Carbon\Carbon;
use DateTime;
use Vinelab\NeoEloquent\Tests\Fixtures\Permission;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class QueryingRelationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('MATCH (n) DETACH DELETE n');
    }

    public function testQueryingNestedHas()
    {
        // user with a role that has only one permission
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['title' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo = User::create(['name' => 'frappe']);
        $roleWithTwo = Role::create(['title' => 'pikachuu']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);

        // user with a role that has no permission
        $user2 = User::Create(['name' => 'u2']);
        $role2 = Role::create(['title' => 'nosperm']);

        $user2->roles()->save($role2);

        // get the users where their roles have at least one permission.
        $found = User::has('roles.permissions')->get();

        $this->assertCount(2, $found);
        $this->assertInstanceOf(User::class, $found[1]);
        $this->assertEquals($userWithTwo->toArray(), $found->where('name', 'frappe')->first()->toArray());
        $this->assertInstanceOf(User::class, $found[0]);
        $this->assertEquals($user->toArray(), $found->where('name', 'cappuccino')->first()->toArray());

        $moreThanOnePermission = User::has('roles.permissions', '>=', 2)->get();
        $this->assertCount(1, $moreThanOnePermission);
        $this->assertInstanceOf(
            User::class,
            $moreThanOnePermission[0]
        );
        $this->assertEquals($userWithTwo->toArray(), $moreThanOnePermission[0]->toArray());
    }

    public function testQueryingWhereHasOne()
    {
        $mrAdmin = User::create(['name' => 'Rundala']);
        $anotherAdmin = User::create(['name' => 'Makhoul']);
        $mrsEditor = User::create(['name' => 'Mr. Moonlight']);
        $mrsManager = User::create(['name' => 'Batista']);
        $anotherManager = User::create(['name' => 'Quin Tukee']);

        $admin = Role::create(['title' => 'admin']);
        $editor = Role::create(['title' => 'editor']);
        $manager = Role::create(['title' => 'manager']);

        $mrAdmin->roles()->save($admin);
        $anotherAdmin->roles()->save($admin);
        $mrsEditor->roles()->save($editor);
        $mrsManager->roles()->save($manager);
        $anotherManager->roles()->save($manager);

        // check admins
        $admins = User::whereHas('roles', function ($q) {
            $q->where('title', 'admin');
        })->get();
        $this->assertCount(2, $admins);
        $expectedAdmins = [$mrAdmin->getKey(), $anotherAdmin->getKey()];
        $this->assertEqualsCanonicalizing($expectedAdmins, $admins->pluck($mrAdmin->getKeyName())->toArray());

        // check editors
        $editors = User::whereHas('roles', function ($q) {
            $q->where('title', 'editor');
        })->get();
        $this->assertCount(1, $editors);
        $this->assertEquals($mrsEditor->toArray(), $editors->first()->toArray());

        // check managers
        $expectedManagers = [$mrsManager->getKey(), $anotherManager->getKey()];
        $managers = User::whereHas('roles', function ($q) {
            $q->where('title', 'manager');
        })->get();
        $this->assertCount(2, $managers);
        $this->assertEqualsCanonicalizing(
            $expectedManagers,
            $managers->pluck($anotherManager->getKeyName())->toArray()
        );
    }

    public function testQueryingWhereHasById()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['title' => 'pikachu']);

        $user->roles()->save($role);

        $found = User::whereHas('roles', function ($q) use ($role) {
            $q->where('title', $role->getKey());
        })->first();

        $this->assertInstanceOf(User::class, $found);
    }

    public function testQueryingNestedWhereHasUsingProperty()
    {
        // user with a role that has only one permission
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['title' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo = User::create(['name' => 'cappuccino0']);
        $roleWithTwo = Role::create(['title' => 'pikachuU']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);

        $found = User::whereHas('roles', function ($q) use ($role, $permission) {
            $q->where('title', $role->title);
            $q->whereHas('permissions', function ($q) use ($permission) {
                $q->where('title', $permission->title);
            });
        })->get();

        $this->assertCount(1, $found);
        $this->assertInstanceOf(User::class, $found->first());
        $this->assertEquals($user->toArray(), $found->first()->toArray());
    }

    public function testSavingRelationWithDateTimeAndCarbonInstances()
    {
        $user = User::create(['name' => 'Andrew Hale']);
        $yesterday = Carbon::now();
        $brother = new User(['name' => 'Simon Hale', 'dob' => $yesterday]);

        $dt = new DateTime();
        $someone = User::create(['name' => 'Producer', 'dob' => $dt]);

        $user->colleagues()->save($someone);
        $user->colleagues()->save($brother);

        $andrew = User::find('Andrew Hale');

        $colleagues = $andrew->colleagues()->get();
        $this->assertEquals(
            $dt->format($andrew->getDateFormat()),
            $colleagues[0]->dob->format($andrew->getDateFormat())
        );
        $this->assertEquals(
            $yesterday->format($andrew->getDateFormat()),
            $colleagues[1]->dob->format($andrew->getDateFormat())
        );
    }
}
