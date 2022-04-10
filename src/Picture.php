<?php

namespace Kinglozzer\SilverstripePicture;

use Exception;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ViewableData;

class Picture extends ViewableData
{
    /**
     * A config array of available styles to be called in templates
     */
    private static array $styles = [];

    /**
     * The underlying Silverstripe image
     */
    protected Image $image;

    /**
     * The requested picture style
     */
    protected string $style;

    /**
     * The config settings for the requested picture style
     */
    protected ?array $styleConfig;

    public function __construct(Image $image, string $style)
    {
        parent::__construct();

        $this->image = $image;
        $this->style = $style;

        $styles = array_change_key_case($this->config()->get('styles'), CASE_LOWER);
        $this->styleConfig = $styles[$this->style];
    }

    public function forTemplate(): DBHTMLText
    {
        return $this->renderWith(__CLASS__);
    }

    public function getSourceImage(): Image
    {
        return $this->image;
    }

    /**
     * @throws Exception
     */
    public function getDefaultImage(): Img
    {
        $defaultConfig = $this->styleConfig['default'] ?? null;
        if (!$defaultConfig) {
            throw new Exception("No default config set for style “{$this->style}”");
        }

        return Img::create($this->image, $defaultConfig);
    }

    /**
     * @throws Exception
     */
    public function getSources(): ArrayList
    {
        $sourcesConfig = $this->styleConfig['sources'] ?? [];
        if (empty($sourcesConfig)) {
            throw new Exception("No sources config set for style “{$this->style}”");
        }

        $sources = ArrayList::create();
        foreach ($sourcesConfig as $media => $sourceConfig) {
            $source = Source::create($this->image, $media, $sourceConfig);
            $sources->push($source);
        }

        return $sources;
    }
}
