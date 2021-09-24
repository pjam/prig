<?php

use pjam\prig\ImageGenerator\ImageGenerator;

require_once '../vendor/autoload.php';

$imageGenerator = new ImageGenerator($_GET);

$imageObject = $imageGenerator->createImage();

$imageGenerator->outputImage($imageObject);
