<?php

declare(strict_types=1);

namespace App\Model;

use Zend\Stdlib\ParametersInterface;

abstract class AbstractModel
{
    /**
     * @var array
     */
    protected static $sets = [];

    /**
     * @param string|array $param
     *
     * @return array
     */
    public static function parseParam($param): array
    {
        if (empty($param)) {
            return $param;
        }

        if (is_string($param)) {
            $param = explode(',', $param);
        }

        // We recreate the hash in order to keep the former order
        $ret = [];
        foreach ($param as $k => $v) {
            if (ctype_digit((string) $k)) {
                $ret[$k] = strtolower(trim($v));
            } else {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    /**
     * @param \Zend\Stdlib\ParametersInterface $params
     *
     * @return \Zend\Stdlib\ParametersInterface
     */
    public static function processParams(ParametersInterface $params, $useDefaultSet = true): ParametersInterface
    {
        // clone $params because $event->getQueryParams() is passed by reference
        // and we want to access it anytime without modification
        $p = clone $params;
        if (
            empty($p['fields'])
            and empty($p['_embedded'])
            and empty($p['sets'])
            and isset(static::$sets['default'])
            and $useDefaultSet
        ) {
            $p['sets'] = 'default';
        }

        if (!empty($p['sets']) and isset(static::$sets[$p['sets']])) {
            foreach (static::$sets[$p['sets']] as $k => $v) {
                $p[$k] = $v;
            }
            unset($p['sets']);
        }

        static::processParamsRecursive($p);

        return $p;
    }

    /**
     * @param \Zend\Stdlib\ParametersInterface|array $params
     */
    protected static function processParamsRecursive(&$params): void
    {
        foreach ($params as $k => &$param) {
            switch (true) {
                case $k === 'fields':
                    $param = static::parseParam($param);
                    break;
                case $k === 'sorting':
                    break;
                case $k === 'sets':
                    break;
                case $k === '_embedded':
                    $param = static::parseParam($param);
                    // break intentionally ommited
                default:
                    if (is_array($param)) {
                        static::processParamsRecursive($param);
                    }
                    break;
            }
        }
    }
}
