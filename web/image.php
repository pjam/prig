<?php

use pjam\prig\ImageGenerator\ImageGenerator;

require_once '../vendor/autoload.php';

$imageGenerator = new ImageGenerator($_GET);

$imageObject = $imageGenerator->createRandomImage();

$imageGenerator->outputImage($imageObject);
