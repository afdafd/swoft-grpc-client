<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client;


class StatusCode
{
    /**
     * 操作成功完成
     */
    const OK = 0;

    /**
     * 操作被取消(通常由调用者)
     */
    const CANCELLED = 1;

    /**
     * 未知错误
     */
    const UNKNOWN = 2;

    /**
     * 客户端指定了无效的参数.  Note that this differs
     * from FAILED_PRECONDITION.  INVALID_ARGUMENT indicates arguments
     * that are problematic regardless of the state of the system
     * (e.g., a malformed file name).
     */
    const INVALID_ARGUMENT = 3;

    /**
     * 操作完成前截止日期已过.  For operations
     * that change the state of the system, this error may be returned
     * even if the operation has completed successfully.  For example, a
     * successful response from a server could have been delayed long
     * enough for the deadline to expire.
     */
    const DEADLINE_EXCEEDED = 4;

    /**
     * 某些被请求的实体(例如，文件或目录)没有找到
     * Some requested entity (e.g., file or directory) was not found.
     */
    const NOT_FOUND = 5;

    /**
     * 试图创建的实体(例如，文件或目录)已经存在
     * Some entity that we attempted to create (e.g., file or directory) already exists.
     */
    const ALREADY_EXISTS = 6;

    /**
     * 调用者没有执行指定操作的权限. PERMISSION_DENIED must not be used for rejections
     * caused by exhausting some resource (use RESOURCE_EXHAUSTED
     * instead for those errors).  PERMISSION_DENIED must not be
     * used if the caller cannot be identified (use UNAUTHENTICATED
     * instead for those errors).
     */
    const PERMISSION_DENIED = 7;

    /**
     * 某些资源已经耗尽, perhaps a per-user quota, or
     * perhaps the entire file system is out of space.
     */
    const RESOURCE_EXHAUSTED = 8;

    /**
     * 操作被拒绝，因为系统不处于执行操作所需的状态.  For example, directory
     * to be deleted may be non-empty, an rmdir operation is applied to
     * a non-directory, etc.
     *
     * <p>A litmus test that may help a service implementor in deciding
     * between FAILED_PRECONDITION, ABORTED, and UNAVAILABLE:
     * (a) Use UNAVAILABLE if the client can retry just the failing call.
     * (b) Use ABORTED if the client should retry at a higher-level
     * (e.g., restarting a read-modify-write sequence).
     * (c) Use FAILED_PRECONDITION if the client should not retry until
     * the system state has been explicitly fixed.  E.g., if an "rmdir"
     * fails because the directory is non-empty, FAILED_PRECONDITION
     * should be returned since the client should not retry unless
     * they have first fixed up the directory by deleting files from it.
     */
    const FAILED_PRECONDITION = 9;

    /**
     * 操作被中止，通常是由于并发性问题，如排序器检查失败、事务中止等
     * The operation was aborted, typically due to a concurrency issue
     * like sequencer check failures, transaction aborts, etc.
     *
     * <p>See litmus test above for deciding between FAILED_PRECONDITION,
     * ABORTED, and UNAVAILABLE.
     */
    const ABORTED = 10;

    /**
     * 操作试图超出有效范围。例如，查找或读取文件的结尾部分
     * Operation was attempted past the valid range.  E.g., seeking or
     * reading past end of file.
     *
     * <p>Unlike INVALID_ARGUMENT, this error indicates a problem that may
     * be fixed if the system state changes. For example, a 32-bit file
     * system will generate INVALID_ARGUMENT if asked to read at an
     * offset that is not in the range [0,2^32-1], but it will generate
     * OUT_OF_RANGE if asked to read from an offset past the current
     * file size.
     *
     * <p>There is a fair bit of overlap between FAILED_PRECONDITION and OUT_OF_RANGE.
     * We recommend using OUT_OF_RANGE (the more specific error) when it applies
     * so that callers who are iterating through
     * a space can easily look for an OUT_OF_RANGE error to detect when they are done.
     */
    const OUT_OF_RANGE = 11;

    /**
     * 此服务中未实现或不支持/启用操作
     * Operation is not implemented or not supported/enabled in this service.
     */
    const UNIMPLEMENTED = 12;

    /**
     * 内部错误.  Means some invariants expected by underlying
     * system has been broken.  If you see one of these errors,
     * something is very broken.
     */
    const INTERNAL = 13;

    /**
     * 该服务目前不可用.  This is a most likely a
     * transient condition and may be corrected by retrying with
     * a backoff. Note that it is not always safe to retry
     * non-idempotent operations.
     *
     * <p>See litmus test above for deciding between FAILED_PRECONDITION,
     * ABORTED, and UNAVAILABLE.
     */
    const UNAVAILABLE = 14;

    /**
     * 无法恢复的数据丢失或损坏
     * Unrecoverable data loss or corruption.
     */
    const DATA_LOSS = 15;

    /**
     * 请求没有操作的有效身份验证凭据
     * The request does not have valid authentication credentials for the
     * operation.
     */
    const UNAUTHENTICATED = 16;

    /**
     * @see https://grpc.github.io/grpc/core/md_doc_statuscodes.html
     */
    const HTTP_CODE_MAPPING = [
        self::OK => 200,
        self::CANCELLED => 499,
        self::UNKNOWN => 500,
        self::INVALID_ARGUMENT => 400,
        self::DEADLINE_EXCEEDED => 504,
        self::NOT_FOUND => 404,
        self::ALREADY_EXISTS => 409,
        self::PERMISSION_DENIED => 403,
        self::RESOURCE_EXHAUSTED => 429,
        self::FAILED_PRECONDITION => 400,
        self::ABORTED => 409,
        self::OUT_OF_RANGE => 400,
        self::UNIMPLEMENTED => 501,
        self::INTERNAL => 500,
        self::UNAVAILABLE => 503,
        self::DATA_LOSS => 500,
        self::UNAUTHENTICATED => 401,
    ];
}
