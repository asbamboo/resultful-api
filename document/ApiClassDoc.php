<?php
namespace asbamboo\restfulApi\document;

/**
 * Api接口类的帮助信息
 *
 * @author 李春寅 <licy2013@aliyun.com>
 * @since 2018年9月15日
 */
class ApiClassDoc implements ApiClassDocInterface
{
    /**
     * @var array
     */
    private $docs;

    /**
     *
     * @var string
     */
    private $path;

    /**
     *
     * @var ApiEntityClassDocInterface|null
     */
    private $ApiEntityClassDoc;

    /**
     *
     * @param string $api_class 参数应该是实现ApiClassInterface的一个类
     * @param string $api_namespace
     */
    public function __construct(/*ApiClassInterface*/ $api_class, string $api_namespace)
    {
        $this->parseDoc($api_class);
        $this->parsePath($api_class, $api_namespace);
    }

    /**
     * 解析注释行
     *
     * @param string $api_class ApiClassInterface的一个类
     */
    private function parseDoc(/*ApiClassInterface*/ $api_class) : void
    {
        $Reflection = new \ReflectionClass($api_class);
        $document   = $Reflection->getDocComment();

        if(preg_match_all('#@(\w+)\s(.*)[\r\n]#siU', $document, $matches)){
            foreach($matches[1] AS $index => $key){
                $this->docs[$key][]   = trim($matches[2][$index]);
            }
        }
    }

    /**
     *
     * @param $parsing $api_class api类名
     * @param string $api_namespace API 存放的命名空间
     */
    private function parsePath(/*ApiClassInterface*/ $api_class, string $api_namespace)
    {
        $api_namespace  = rtrim($api_namespace, '\\');
        $parsing        = preg_replace(addslashes("@{$api_namespace}\\([^\\]+)\\@siU"), '\\', $api_class);
        $parsing        = explode('\\', $parsing);
        $parsing        = array_map(function($p){
            return strtolower(trim(preg_replace('@([A-Z])@', '-$1', $p), '-'));
        },$parsing);

        $this->path = implode('/', $parsing);
    }

    /**
     *
     * {@inheritDoc}
     * @see \asbamboo\restfulApi\document\ApiClassDocInterface::getName()
     */
    public function getName() : string
    {
        return isset($this->docs['name']) ? implode('\r\n', $this->docs['name']) : '';
    }

    /**
     *
     * {@inheritDoc}
     * @see \asbamboo\restfulApi\document\ApiClassDocInterface::getPath()
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     *
     * {@inheritDoc}
     * @see \asbamboo\restfulApi\document\ApiClassDocInterface::isDelete()
     */
    public function isDelete() : bool
    {
        return isset($this->docs['delete']);
    }

    /**
     *
     * {@inheritDoc}
     * @see \asbamboo\restfulApi\document\ApiClassDocInterface::getEntityClass()
     */
    public function getEntityClass() : string
    {
        return isset($this->docs['entity']) ? current( $this->docs['entity'] ) : '';
    }

    /**
     *
     * {@inheritDoc}
     * @see \asbamboo\restfulApi\document\ApiClassDocInterface::getApiEntityClassDoc()
     */
    public function getApiEntityClassDoc() : ?ApiEntityClassDocInterface
    {
        if(!$this->ApiEntityClassDoc){
            $this->ApiEntityClassDoc    = $this->getEntityClass() ? new ApiEntityClassDoc($this->getEntityClass()) : null;
        }
        return $this->ApiEntityClassDoc;
    }
}