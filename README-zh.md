# hyperf-redis-lock
[![Latest Stable Version](https://poser.pugx.org/Lysice/hyperf-redis-lock/v/stable)](https://packagist.org/packages/Lysice/hyperf-redis-lock)
[![Total Downloads](https://poser.pugx.org/Lysice/hyperf-redis-lock/downloads)](https://packagist.org/packages/Lysice/hyperf-redis-lock)
[![Latest Unstable Version](https://poser.pugx.org/Lysice/hyperf-redis-lock/v/unstable)](https://packagist.org/packages/Lysice/hyperf-redis-lock)
[![License](https://poser.pugx.org/Lysice/hyperf-redis-lock/license)](https://packagist.org/packages/Lysice/hyperf-redis-lock)
[English](./README.md) | 中文

一个简单的Redis分布式锁的实现 基于Hyperf框架。本扩展实现了基本的分布式锁，支持阻塞式分布式锁和非阻塞式分布式锁。(基于) [Lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) 修改，支持php8.

## 原理
`Redis`的命令为原子性 使用`Redis`的`set`即可保证业务的串行执行。
`2.8`之前版本的`Redis`不支持`set` 的`ex`选项 因此只能使用 `setnx+expire`的方式。 对应扩展1.0版本.
`2.8`之后版本的`Redis`可以直接使用 `set` 的`nx+ex`选项。对应扩展2.*版本 (这里直接使用了新语法，原子性)

因此 2.*版本只支持2.8版本以后的`Redis` 1.*版本支持所有版本的`Redis`.

## 确认你的Redis版本 如果你的Redis低于 2.8版本 则set 命令不支持 ex选项 因此你需要安装1.0版本。
`redis-server --version`

## 安装 
注意:请根据你的`Redis`版本
执行 `composer require pizsd/hyperf-redis-lock`

## 使用
首先需要在程序内初始化你需要的`redis`
```
/**
     * @var RedisLock
     */
    protected $redis;

    public function __construct(RedisFactory $redisFactory)
    {
        $this->redis = $redisFactory->get('default');
    }
```

- 非阻塞式锁 该方法在尝试获取锁之后直接返回结果。若获取到锁则执行闭包后返回结果。否则返回false
```
public function lock(ResponseInterface $response)
{
    $lock = new RedisLock();
    $res = $lock->get(lock, 20 *100, function () {
        sleep(10);
        return [123];
    });
    return $response->json($res);
}
```
- 阻塞式锁 该方法首先尝试获取锁，若获取失败 则每隔250毫秒获取一次 直到超时(等待时间超出本程序内锁的过期时间 则判定为超时)。如果锁获取成功 则执行闭包函数返回结果。
注意 若超时 则程序会抛出`LockTimeoutException`超时异常。应用程序内需要自己捕获该异常以便处理超时情况的返回结果。
例子:
```
/**
 * @return \Psr\Http\Message\ResponseInterface
 */
public function lockA(ResponseInterface $response)
{
    try {
        $lock = new RedisLock();
        $res = $lock->block('lock', 44, function () {
            return [456];
        });
        return $response->json(['res' => $res]);
    // catch the exception
    } catch (LockTimeoutException $exception) {
        var_dump('lockA lock check timeout');
        return $response->json(['res' => false, 'message' => 'timeout']);
    }
}
```


## 最后

#### 代码贡献
如果存在任何好的想法请提交pull request
Feel free to create a fork and submit a pull request if you would like to contribute.

#### 代码问题
如果存在任何问题请提交issue.
