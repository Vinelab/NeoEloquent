<?php

namespace Nesk\Rialto;

use Symfony\Component\Process\Process;
use Psr\Log\{LoggerInterface, LogLevel};

class Logger implements LoggerInterface
{
    /**
     * The original logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     */
    public function __construct(?LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function emergency($message, array $context = []): void {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function alert($message, array $context = []): void {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function critical($message, array $context = []): void {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function error($message, array $context = []): void {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function warning($message, array $context = []): void {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function notice($message, array $context = []): void {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function info($message, array $context = []): void {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     */
    public function debug($message, array $context = []): void {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $level
     * @param string $message
     */
    public function log($level, $message, array $context = []): void {
        if ($this->logger instanceof LoggerInterface) {
            $message = $this->interpolate($message, $context);
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Interpolate context values into the message placeholders.
     *
     * @see https://www.php-fig.org/psr/psr-3/#12-message
     */
    protected function interpolate(string $message, array $context = []): string {
        $replace = array();

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
