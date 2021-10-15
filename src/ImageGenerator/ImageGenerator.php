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
        'type' => 'jpeg',
        'numColors' => null,
    ];

    /**
     * @var int
     */
    private const MAX_COLOR_COUNT = 256;

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

        if (isset($requestParams['numColors']) && is_numeric($requestParams['numColors']) && $this->isColorCountValid($requestParams['numColors'])) {
            $validParams['numColors'] = (int)$requestParams['numColors'];
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
     * @param int $numColors
     *
     * @return bool
     */
    private function isColorCountValid(int $numColors): bool
    {
        return ($numColors <= static::MAX_COLOR_COUNT);
    }

    /**
     * @return \GdImage
     */
    public function createRandomImage(): GdImage
    {
        $image = imagecreatetruecolor($this->params['width'], $this->params['height']);
        $colorsArray = $this->buildColorsArray($image);

        $colorCounter = 0;
        for ($heightPixel = 0; $heightPixel < $this->params['height']; $heightPixel ++) {
            for ($widthPixel = 0; $widthPixel < $this->params['width']; $widthPixel ++) {
                $color = $colorsArray[$colorCounter ++];

                imagesetpixel($image, $widthPixel, $heightPixel, $color);
            }
        }

        return $image;
    }

    /**
     * @param \GdImage $imageObject
     *
     * @return int[]
     */
    private function buildColorsArray(GdImage $imageObject): array
    {
        $imageSize = $this->params['width'] * $this->params['height'];
        $colorCount = $this->params['numColors'] ?: $imageSize;

        $colors = [];

        for ($colorIndex = 0; $colorIndex < $colorCount; $colorIndex ++) {
            $colors[] = $this->getRandomColor($imageObject);
        }

        if ($colorCount === $imageSize) {
            return $colors;
        }

        $colorsArray = [];
        for ($pixelCount = 0; $pixelCount < $imageSize; $pixelCount ++) {
            $colorsArray[] = $colors[array_rand($colors, 1)];
        }

        return $colorsArray;
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
                    imagejpeg($imageObject, null, 50);
                    break;
                case 'webp':
                    imagewebp($imageObject, null, 50);
                    break;
                case 'png':
                default:
                    imagepng($imageObject, null, 7);
            }

            imagedestroy($imageObject);
        } catch (Throwable $exception) {
            error_log('ERROR: ' . $exception->getMessage());
        }
    }

    /**
     * @param \GdImage $imageObject
     *
     * @return string
     */
    public function getEncodedImage(GdImage $imageObject): string
    {
        ob_start();

        switch ($this->params['type']) {
            case 'jpeg':
                imagejpeg($imageObject, null, 50);
                break;
            case 'webp':
                imagewebp($imageObject, null, 50);
                break;
            case 'png':
            default:
                imagepng($imageObject, null, 7);
        }

        $imageData = ob_get_clean();

        return sprintf(
            'data:image/%s;base64,%s',
            $this->params['type'],
            base64_encode( $imageData )
        );
    }
}
