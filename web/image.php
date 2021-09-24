<?php
/**
 * @var int
 */
const MAX_SIZE = 2000;

/**
 * @var int
 */
const MIN_SIZE = 1;

/**
 * @var string[]
 */
const TYPES = [
    'jpg',
    'jpeg',
    'png',
    'webp',
];

$defaultParams = [
    'height' => 200,
    'width' => 200,
    'type' => 'png',
];

$params = array_merge($defaultParams, getValidParams());

/**
 * @return array
 */
function getValidParams(): array
{
    $validParams = [];
    if (isset($_GET['height']) && is_numeric($_GET['height']) && ($_GET['height'] >= MIN_SIZE) && ($_GET['height'] <= MAX_SIZE)) {
        $validParams['height'] = (int)$_GET['height'];
    }

    if (isset($_GET['width']) && is_numeric($_GET['width']) && ($_GET['width'] >= MIN_SIZE) && ($_GET['width'] <= MAX_SIZE)) {
        $validParams['width'] = (int)$_GET['width'];
    }

    if (isset($_GET['type']) && in_array($_GET['type'], TYPES, true)) {
        $validParams['type'] = str_replace('jpg', 'jpeg', $_GET['type']);
    }

    return $validParams;
}

if ($_GET['debug'] ?? false) {
    error_log('Params: ' . var_export($params, true));
}

/**
 * @param array $params
 *
 * @return \GdImage
 */
function createImage(array $params): GdImage
{
    $image = imagecreatetruecolor($params['width'], $params['height']);

    for ($heightPixel = 0; $heightPixel < $params['height']; $heightPixel ++) {
        for ($widthPixel = 0; $widthPixel < $params['width']; $widthPixel ++) {
            $color = getRandomColor($image);

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
function getRandomColor(GdImage $image): int
{
    $red = random_int(0, 255);
    $green = random_int(0, 255);
    $blue = random_int(0, 255);

    if ($_GET['debug'] ?? false) {
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
 * @param string $type
 *
 * @return void
 */
function outputImage(GdImage $imageObject, string $type): void
{
    header('Content-Type: image/' . $type);

    try {
        switch ($type) {
            case 'png':
                imagepng($imageObject);
                break;
            case 'jpeg':
                imagejpeg($imageObject);
                break;
            case 'webp':
                imagewebp($imageObject);
                break;
            default:
                error_log('Could not find image type' . $type);
        }
    } catch (Throwable $exception) {
        error_log('ERROR: ' . $exception->getMessage());
    }
}

$imageObject = createImage($params);

outputImage($imageObject, $params['type']);
imagedestroy($imageObject);
