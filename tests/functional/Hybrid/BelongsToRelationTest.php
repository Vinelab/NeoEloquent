<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\Hybrid;

use Illuminate\Database\Schema\Blueprint;
use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Relations\Hybrid\HybridRelations;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Invitation extends Eloquent
{
    use HybridRelations;

    protected $connection = "sqlite";
    protected $fillable = ['name', 'mobile', 'member_id', 'sender_id'];

    public function member()
    {
        return $this->belongsToHybrid(Member::class, 'member_id');
    }

    public function sender()
    {
        return $this->belongsTo(Account::class);
    }
}

class Account extends Eloquent
{
    use HybridRelations;

    protected $connection = "sqlite";
    protected $fillable = ['name'];

    public function member()
    {
        return $this->hasOneHybrid(Member::class);
    }
}

class Member extends NeoEloquent
{
    use HybridRelations;

    protected $label = 'member';
    protected $table = 'member';
    protected $connection = "neo4j";
    protected $fillable = ['name', 'account_id'];

    public function invitation()
    {
        return $this->hasOneHybrid(Invitation::class, 'member_id');
    }

    public function account()
    {
        return $this->belongsToHybrid(Account::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'member');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'father');
    }

    public function father()
    {
        return $this->belongsTo(self::class, 'father');
    }
}

class Contact extends NeoEloquent
{
    use HybridRelations;

    protected $label = 'contact';
    protected $table = 'contact';
    protected $connection = "neo4j";
    protected $fillable = ['email'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member');
    }
}


class BelongsToRelationTest extends TestCase
{
    protected $db;

    protected $schema;


    public function tearDown()
    {
        M::close();
        $this->schema->dropIfExists('invitations');
        $this->schema->dropIfExists('accounts');
        Member::where("id", ">", -1)->delete();

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        $this->prepareDatabase();
        Invitation::setConnectionResolver($this->resolver);
        Member::setConnectionResolver($this->resolver);

        $this->schema->create('accounts', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->timestamps();
        });
        $this->schema->create('invitations', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->string('mobile');
            $t->integer('member_id');
            $t->integer('sender_id')->nullable();
            $t->timestamps();
        });
    }

    public function testLazyLoadingBelongsToDepth1()
    {
        $account = Account::create(["name" => "yassir"]);
        $member = Member::create(["name" => "yassir", "account_id" => $account->id]);

        $this->assertEquals($member->account->id, $account->id);
    }

    public function testLazyLoadingBelongsToDepth2()
    {
        $this->markTestSkipped();
        $account = Account::create(["name" => "yassir"]);
        $member = Member::create(["name" => "yassir", "account_id" => $account->id]);
        $contact = $member->contacts()->save(Contact::create(["email" => "yassir.awad@dce.sa"]))->related();
    }

    public function testEgarLoadingBelongsToUsginLoadDepth2()
    {
        $account = Account::create(["name" => "yassir"]);
        $member = Member::create(["name" => "yassir", "account_id" => $account->id]);
        $invitation = Invitation::create(['name' => 'Daughter', 'sender_id' => $account->id, 'mobile' => '0565656', "member_id" => $member->id]);

        $this->assertEquals($member->id, $invitation->load("sender.member")->sender->member->id);
        $this->assertEquals($account->id, $invitation->load("member.account")->member->account->id);
    }

    public function testEgarLoadingUsingWithDepth1()
    {
        $account = Account::create(["name" => "yassir"]);
        Member::create(["name" => "yassir", "account_id" => $account->id]);
        $member = Member::with(["account"])->first();

        $this->assertTrue($member->relationLoaded("account"));
        $this->assertEquals($member->account->id, $account->id);
    }

    public function testEgarLoadingUsingWithDepth2()
    {
        $account = Account::create(["name" => "yassir"]);
        $member = Member::create(["name" => "yassir", "account_id" => $account->id]);
        $member->contacts()->save(Contact::create(["email" => "yassir.awad@dce.sa"]));
        $contact = Contact::with(["member.account"])->first();

        $this->assertTrue($contact->relationLoaded("member"));
        $this->assertEquals($contact->member->id, $member->id);
        $this->assertTrue($contact->member->relationLoaded("account"));
        $this->assertEquals($contact->member->account->id, $account->id);
    }
}
