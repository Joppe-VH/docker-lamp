<?php

namespace Upload;

use Upload\MimeType;
use Upload\FileSizeUnits as FS;
use Upload\UploadError;

class Upload
{
    private readonly string $p_filePath;
    private int $p_maxSize = 2097152; /* 2MB */
    private MimeType|string $p_type;
    private array $allowedTypes = [];
    private string $finalDest;

    public function __construct(public readonly string $postName) {}

    public function name()
    {
        return explode('.', $_FILES[$this->postName]['name'])[0];
    }

    public function maxSize()
    {
        return $this->p_maxSize;
    }

    public function setMaxSize(int $bytes)
    {
        $this->p_maxSize = $bytes;
        return $this;
    }

    public function hasFile()
    {
        return !empty($_FILES[$this->postName]['name']);
    }

    public function filePath()
    {
        return ($this->p_filePath ??= $_FILES[$this->postName]['tmp_name']);
    }

    public function size(): int|false
    {
        return filesize($this->filePath());
    }

    public function type(): MimeType|string
    {
        return ($this->p_type ??= MimeType::fromFile($this->filePath()));
    }

    public function isEmpty(): bool
    {
        return $this->size() === 0;
    }

    public function isTooLarge(): bool
    {
        return $this->size() > $this->maxSize();
    }

    public function isWrongType(): bool
    {
        return !in_array($this->type(), $this->allowedTypes);
    }

    public function setAllowedType(MimeType $type): self
    {
        if (!in_array($type, $this->allowedTypes))
            $this->allowedTypes[] = $type;
        return $this;
    }

    public function getStatus(): UploadError
    {
        return UploadError::from($_FILES[$this->postName]['error']);
    }

    public function getError(): UploadError
    {
        if ($this->getStatus() !== UploadError::OK) return $this->getStatus();
        if ($this->isEmpty()) return UploadError::EMPTY;
        if ($this->isTooLarge()) return UploadError::TOO_LARGE;
        if ($this->isWrongType()) return UploadError::WRONG_TYPE;
        return UploadError::OK;
    }

    public function hasError(): bool
    {
        return $this->getError() !== UploadError::OK;
    }

    function getErrorMsg()
    {
        return match ($error = $this->getError()) {
            UploadError::TOO_LARGE => $error->message() . '. Your file is ' . FS::format($this->size()) . '. The allowed maximum is: ' . FS::format($this->maxSize()),
            UploadError::WRONG_TYPE => $error->message() . '. The allowed types are: ' . implode(', ', array_map(fn($t) => $t->name(), $this->allowedTypes)),
            default => $error->message()
        };
    }

    public function getRandFileName(): string
    {
        return uniqid() . $this->type()->extension();
    }

    public function move(string $directory, string $filename = null): string|false
    {
        $filename ??= $this->getRandFileName();
        $filePath = "$directory/$filename";
        $success = move_uploaded_file($this->filePath(), $_SERVER['DOCUMENT_ROOT'] . $filePath);
        if (!$success) return false;
        $this->finalDest = $filePath;
        return $this->getFinalDest();
    }

    public function getFinalDest()
    {
        return $this->finalDest ?? '';
    }

    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }
}
