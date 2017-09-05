<?php
namespace features\CurrencyRates\Support;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

trait ConsoleContextTrait
{
    /** @var  string */
    protected $consoleOutput;

    /**
     * @return KernelInterface
     */
    abstract public function getKernel();

    /**
     * @When I run :command command
     */
    public function iRunCommand($command)
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $parts = explode(" ", $command);
        $inputData = [
            'command' => array_shift($parts)
        ];
        foreach ($parts as $arg) {
            list($argName, $argValue) = explode('=', $arg);
            $inputData[$argName] = $argValue;
        }

        $input = new ArrayInput($inputData);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $this->consoleOutput = $output->fetch();
    }

    /**
     * @When I see command output :output
     */
    public function iSeeCommandOutput($output)
    {
        if (strpos($this->consoleOutput, $output) === false) {
            throw new \Exception(sprintf("'%s' was not found in console output '%s'", $output, $this->consoleOutput));
        };
    }

    /**
     * @return string
     */
    protected function getConsoleOutput()
    {
        return $this->consoleOutput;
    }
}
