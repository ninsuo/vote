<?php

namespace App\Twig\Extension;

use App\Repository\SettingRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SettingsExtension extends AbstractExtension
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var array
     */
    private $settings;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('settings', [$this, 'settings']),
        ];
    }

    public function settings($property, $default = null)
    {
        if (isset($this->settings[$property])) {
            return $this->settings[$property];
        }

        $value                     = $this->settingRepository->get($property, $default);
        $this->settings[$property] = $value;

        return $value;
    }
}