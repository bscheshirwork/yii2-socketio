<?php

namespace bscheshirwork\socketio;

use Yii;
use yii\base\Component;

final class EventManager extends Component
{
    /**
     * Array of events namespaces
     *
     * @var array
     */
    public array $namespaces = [];

    /**
     * You can set unique nsp for channels
     *
     * @var string
     */
    public string $nsp = '';

    /**
     * List with all events
     */
    private static array $list = [];

    public function getList(): array
    {
        if (empty(self::$list)) {
            foreach ($this->namespaces as $namespace) {
                $alias = Yii::getAlias('@' . str_replace('\\', '/', trim((string) $namespace, '\\')));
                foreach (glob(sprintf('%s/**.php', $alias)) as $file) {
                    $className = sprintf('%s\%s', $namespace, basename($file, '.php'));
                    if (method_exists($className, 'name')) {
                        self::$list[$className::name()] = $className;
                    }
                }
            }
        }

        return self::$list;
    }
}
