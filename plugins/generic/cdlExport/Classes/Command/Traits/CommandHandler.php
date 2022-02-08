<?php namespace CdlExportPlugin\Command\Traits;

trait CommandHandler {

    private $args;

    private $handlerInitialized = false;

    public function initializeHandler($args) {
        $this->args = $args;
        $this->handlerInitialized = true;
    }

    public function execute() {
        if(!$this->handlerInitialized) throw new \Exception('Handler not initialized. Call `initializeHandler()` in your class.');
        $cmd = array_shift($this->args);
        if(strlen($cmd) > 0) {
            if($this->allowedCommand($cmd)) {
                $this->executeCommand($cmd, $this->args);
            } else {
                $this->executeError($cmd);
            }
            return;
        }
        $this->usage();
    }

    /**
     * @param $cmd
     * @return mixed
     */
    protected function allowedCommand($cmd) {
        return array_key_exists($cmd, $this->allowedCommands);
    }

    protected function executeError($cmd) {
        throw new \Exception("Cannot execute command $cmd");
    }

    /**
     * @param $cmd
     * @return mixed
     */
    protected function executeCommand($cmd, $args) {
        $commandClass = $this->allowedCommands[$cmd];
        $commandInstance = new $commandClass($args);
        return $commandInstance->execute();
    }
}