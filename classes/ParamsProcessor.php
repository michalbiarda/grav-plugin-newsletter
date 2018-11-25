<?php

namespace Grav\Plugin\Newsletter;

class ParamsProcessor
{
    /**
     * @param string $key
     * @param array $default
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getStringsArrayParamValue(string $key, array $default, $params = []): array
    {
        if (!empty($params[$key])) {
            if (!is_array($params[$key])) {
                throw new \InvalidArgumentException(sprintf('"%s" must be an array of strings.', $key));
            }
            foreach ($params[$key] as $value) {
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(sprintf('"%s" must be an array of strings.', $key));
                }
            }
            return $params[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param string $default
     * @param array $params
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getStringParamValue(string $key, string $default, $params = []): string
    {
        if (!empty($params[$key])) {
            if (!is_string($params[$key])) {
                throw new \InvalidArgumentException(sprintf('"%s" must be a string.', $key));
            }
            return $params[$key];
        }
        return $default;
    }
}