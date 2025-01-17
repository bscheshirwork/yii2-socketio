<?php

namespace bscheshirwork\socketio;

use yii\web\AssetBundle;

/**
 * Access Message asset bundle.
 */
final class SocketIoAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/bscheshirwork/yii2-socketio/server/node_modules/socket.io-client/dist';

    /**
     * @var array
     */
    public $js = ['socket.io.js'];
}
