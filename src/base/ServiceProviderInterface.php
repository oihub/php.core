<?php

namespace oihub\base;

/**
 * Interface ServiceProviderInterface.
 * 
 * @author sean <maoxfjob@163.com>
 */
interface ServiceProviderInterface
{
    /**
     * 注册服务.
     * @param Container $container 容器.
     * @return mixed
     */
    public function register(Container $container);
}
