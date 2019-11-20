<?php
/*

 About:
 Author: Raul Perusquia <raul@inikoo.com>
 Created: 2019-11-20T14:22:49+01:00, Malaga Spain

 Copyright (c) 2019, Inikoo

 Version 1.0
*/

use Gumlet\ImageResize;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Exceptions\Node;
use Spatie\ImageOptimizer\OptimizerChainFactory;


function post_process_screenshot($filename, $device, $type) {


    $size_data = getimagesize('tmp/'.$filename);
    $width     = $size_data[0];
    $height    = $size_data[1];

    $resized_image_filename = 'tmp/_'.$filename;

    switch ($device) {


        case 'Desktop':


            switch ($type) {
                case 'Full':
                    $width_resize  = $width * .52;
                    $height_resize = $height * .52;
                    break;
                case 'Full_Thumbnail':

                    $ratio                  = $height / $width;

                    $width_resize  = 270;
                    $height_resize = 270 * $ratio;


                    break;
                default:

                    $ratio = $width / $height;

                    $width_resize  = 270;
                    $height_resize = 270 * $ratio;
            }


            break;
        case 'Tablet':

            $ratio = $width / $height;

            $width_resize  = 270;
            $height_resize = 270 * $ratio;
            break;
        case 'Mobile':

            $ratio         = $width / $height;
            $height_resize = 270;
            $width_resize  = 270 / $ratio;

            break;
    }

    $image              = new ImageResize('tmp/'.$filename);
    $image->quality_jpg = 100;
    $image->quality_png = 9;

    $image->resizeToBestFit($width_resize, $height_resize);


    $image->save($resized_image_filename);


    if (file_exists($resized_image_filename)) {
        usleep(1000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(2000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(3000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(100000);
    }


    $optimizerChain = OptimizerChainFactory::create();
    $optimizerChain->optimize($resized_image_filename);

    if (file_exists($resized_image_filename)) {
        usleep(1000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(2000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(3000);
    }
    if (file_exists($resized_image_filename)) {
        usleep(100000);
    }

    unlink('tmp/'.$filename);
    return $resized_image_filename;



}


function take_screenshots($url, $device, $type) {


    $tmp_file = sprintf('original_%d_%s', gmdate('U'), md5($url)).'.jpg';


    $puppeteer = new Puppeteer;
    $browser   = $puppeteer->launch(
        array(
            'headless' => true,
            'args'     => array(
                '--no-sandbox',
                '--disable-setuid-sandbox'
            )
        )
    );

    $page = $browser->newPage();

    if ($device == 'Desktop') {

        if ($type == 'Full') {
            $page->setViewport(
                array(
                    'width'  => 1366,
                    'height' => 1024
                )
            );
        } else {
            $page->setViewport(
                array(
                    'width'  => 1366,
                    'height' => 1024
                )
            );
        }


    } elseif ($device == 'Mobile') {
        $page->emulate(
            array(
                'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',

                'viewport' => array(
                    'width'             => 375,
                    'height'            => 667,
                    'deviceScaleFactor' => 2,
                    'isMobile'          => true,
                    'hasTouch'          => true,
                    'isLandscape'       => false
                )
            )
        );

    } elseif ($device == 'Tablet') {
        $page->emulate(
            array(
                'userAgent' => 'Mozilla/5.0 (iPad; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.34 (KHTML, like Gecko) Version/11.0 Mobile/15A5341f Safari/604.1',

                'viewport' => array(
                    'width'             => 1024,
                    'height'            => 768,
                    'deviceScaleFactor' => 2,
                    'isMobile'          => true,
                    'hasTouch'          => true,
                    'isLandscape'       => true
                )
            )
        );

    }


    try {
        $page->tryCatch->goto(
            $url, array(
                    'timeout'   => 120000,
                    'waitUntil' => 'networkidle0'
                )
        );
    } catch (Node\Exception $exception) {
        return false;
    }


    $screenshot_data = array(
        'path' => 'tmp/'.$tmp_file,
        'type' => 'jpeg'
    );

    if ($type == 'Full') {
        $screenshot_data['fullPage'] = true;
    }

    $page->screenshot($screenshot_data);


    return post_process_screenshot($tmp_file, $device, $type);



}

