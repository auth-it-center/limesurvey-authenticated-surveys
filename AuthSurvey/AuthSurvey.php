<?php

/**
 * LimeSurvey Auth permited Surveys
 *
 * This plugin forces selected surveys to
 * be displayed/submited only to/by authenticated users
 *
 * Author: Panagiotis Karatakis <karatakis@it.auth.gr>
 * Licence: GPL3
 *
 * Sources:
 * https://manual.limesurvey.org/Plugins_-_advanced
 * https://manual.limesurvey.org/Plugin_events
 * https://medium.com/@evently/creating-limesurvey-plugins-adcdf8d7e334
 */

class AuthSurvey extends Limesurvey\PluginManager\PluginBase
{
    protected $storage = 'DbStorage';
    static protected $description = 'This plugin forces selected surveys to be displayed/submited only to/by authenticated users';
    static protected $name = 'AuthSurvey';

    protected $settings = [];

    public function init()
    {
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveyPage');
    }

    public function beforeSurveySettings()
    {
        $event = $this->event;
        $current = $this->get('auth_protection_enabled', 'Survey', $event->get('survey'));
        $event->set('surveysettings.' . $this->id, [
            'name' => get_class($this),
            'settings' => [
                'auth_protection_enabled' => [
                    'type' => 'checkbox',
                    'label' => 'Enabled',
                    'help' => 'Only authenticated users should see the survey ?',
                    'default' => false,
                    'current' => $current,
                ]
            ]
        ]);
    }

    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value)
        {
            $default = $event->get($name, null, null, isset($this->settings[$name]['default']));
            $this->set($name, $value, 'Survey', $event->get('survey'), $default);
        }
    }

    public function beforeSurveyPage()
    {
        $event = $this->event;
        $id = $event->get('surveyId');
        $flag = $this->get('auth_protection_enabled', 'Survey', $id);
        if ($flag) {
            // Check if user is authenticated
            if (is_null(Yii::app()->user->getId())) {
                throw new CHttpException(401, gT("We are sorry but you dont have permissions to do this."));
            }
        }
    }
}