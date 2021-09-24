<?php

namespace pjam\prig\ImageGenerator;

use GdImage;
use Throwable;

class ImageGenerator
{
    /**
     * @var int
     */
    private const MAX_SIZE = 2000;

    /**
     * @var int
     */
    private const MIN_SIZE = 1;

    /**
     * @var string[]
     */
    private const TYPES = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * @var array
     */
    private const DEFAULT_PARAMS = [
        'height' => 200,
        'width' => 200,
        'type' => 'png',
    ];

    private array $params;

    private bool $isDebug;

    public function __construct(array $requestParams = [])
    {
        $this->params = $this->initializeParams($requestParams);

        $this->isDebug = (bool)($this->params['debug'] ?? false);

        if ($this->isDebug) {
            error_log('Params: ' . var_export( $this->params, true));
        }
    }

    /**
     * @param array $requestParams
     *
     * @return array
     */
    private function initializeParams(array $requestParams): array
    {
        return array_merge(static::DEFAULT_PARAMS, $this->normalizeParams($requestParams));
    }

    /**
     * @return array
     */
    private function normalizeParams(array $requestParams): array
    {
        $validParams = [];

        if (isset($requestParams['height']) && is_numeric($requestParams['height']) && $this->isSizeValid($requestParams['height'])) {
            $validParams['height'] = (int)$requestParams['height'];
        }

        if (isset($requestParams['width']) && is_numeric($requestParams['width']) && $this->isSizeValid($requestParams['width'])) {
            $validParams['width'] = (int)$requestParams['width'];
        }

        if (isset($requestParams['type']) && in_array($requestParams['type'], static::TYPES, true)) {
            $validParams['type'] = str_replace('jpg', 'jpeg', $requestParams['type']);
        }

        return $validParams;
    }

    /**
     * @param int $size
     *
     * @return bool
     */
    private function isSizeValid(int $size): bool
    {
        return ($size >= static::MIN_SIZE) && ($size <= static::MAX_SIZE);
    }

    /**
     * @return \GdImage
     */
    public function createImage(): GdImage
    {
        $image = imagecreatetruecolor($this->params['width'], $this->params['height']);

        for ($heightPixel = 0; $heightPixel < $this->params['height']; $heightPixel ++) {
            for ($widthPixel = 0; $widthPixel < $this->params['width']; $widthPixel ++) {
                $color = $this->getRandomColor($image);

                imagesetpixel($image, $widthPixel, $heightPixel, $color);
            }
        }

        return $image;
    }

    /**
     * @param \GdImage $image
     *
     * @return int
     */
    private function getRandomColor(GdImage $image): int
    {
        $red = random_int(0, 255);
        $green = random_int(0, 255);
        $blue = random_int(0, 255);

        if ($this->isDebug) {
            error_log('Red:' .  $red . ', Green: ' . $green . ', Blue: ' . $blue);
        }

        $color = imagecolorallocate($image, $red, $green, $blue);
        imagecolordeallocate($image, $color);

        if(imagecolorstotal($image)>=255) {
            error_log('color limit reached');
        }

        return $color;
    }

    /**
     * @param \GdImage $imageObject
     *
     * @return void
     */
    public function outputImage(GdImage $imageObject): void
    {
        header('Content-Type: image/' . $this->params['type']);

        try {
            switch ($this->params['type']) {
                case 'jpeg':
                    imagejpeg($imageObject);
                    break;
                case 'webp':
                    imagewebp($imageObject);
                    break;
                case 'png':
                default:
                    imagepng($imageObject);
            }

            imagedestroy($imageObject);
        } catch (Throwable $exception) {
            error_log('ERROR: ' . $exception->getMessage());
        }
    }
}
