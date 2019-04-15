<?php

declare(strict_types=1);

require_once __DIR__ . '/DebugHelper.php';
require_once __DIR__ . '/BVIPRCPClass.php';  // diverse Klassen

eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/BufferHelper.php') . '}');
eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/ParentIOHelper.php') . '}');
eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/SemaphoreHelper.php') . '}');
eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/VariableHelper.php') . '}');
eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/VariableProfileHelper.php') . '}');
eval('namespace bvip {?>' . file_get_contents(__DIR__ . '/helper/UTF8Helper.php') . '}');

