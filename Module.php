<?php

namespace achertovsky\debug;

use Yii;
use yii\debug\Module as CoreModule;

class Module extends CoreModule
{
    public $defaultPanel = 'profiling';
    public $historySize = 10000;
    public $dataPath = '@root/frontend/runtime/debug';
    
    public function bootstrap($app)
    {
        $logTarget = new $this->logTarget($this);
        parent::bootstrap($app);
        $this->logTarget = Yii::$app->getLog()->targets['debug'] = $logTarget;
    }
    
    /**
     * Checks if current user is allowed to access the module
     * @return boolean if access is granted
     */
    protected function checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' ||
            $filter === $ip ||
            (($pos = strpos($filter, '*')) !== false &&
            !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        foreach ($this->allowedHosts as $hostname) {
            $filter = gethostbyname($hostname);
            if ($filter === $ip) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @return array default set of panels
     */
    protected function corePanels()
    {
        $panels = [
            'config' => ['class' => 'yii\debug\panels\ConfigPanel'],
            'request' => ['class' => 'yii\debug\panels\RequestPanel'],
            'log' => ['class' => 'yii\debug\panels\LogPanel'],
            'profiling' => ['class' => 'yii\debug\panels\ProfilingPanel'],
            'db' => ['class' => 'yii\debug\panels\DbPanel'],
            'assets' => ['class' => 'yii\debug\panels\AssetPanel'],
            'mail' => ['class' => 'yii\debug\panels\MailPanel'],
            'timeline' => ['class' => 'yii\debug\panels\TimelinePanel'],
        ];

        if (php_sapi_name() !== 'cli') {
            $components = Yii::$app->getComponents();
            if (isset($components['user']['identityClass'])) {
                $panels['user'] = ['class' => 'yii\debug\panels\UserPanel'];
            }
            $panels['router'] = ['class' => 'yii\debug\panels\RouterPanel'];
        }

        return $panels;
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->dataPath = Yii::getAlias($this->dataPath);
        $this->initPanels();
    }
}
