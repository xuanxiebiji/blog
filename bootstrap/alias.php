<?php
/**
 * Created by PhpStorm.
 * User: chenbingjie
 * Date: 2018/2/8
 * Time: 上午9:59
 */
//添加映射关系      'controller' => 'app/controller',
Start::$auto->addMaps('controller','app/controller');
Start::$auto->addMaps('model','app/model');
Start::$auto->addMaps('framework','vendor/lib/framework/src');

