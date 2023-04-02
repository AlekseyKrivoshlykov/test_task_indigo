<?php

namespace App\Entity;

use App\Repository\PostgresPDORepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PDO;

#[ORM\Entity(repositoryClass: PostgresPDORepository::class)]
class PostgresPDO
{
    const HOST             = 'localhost';

    const PORT             = 5433;

    const DATA_BASE_NAME   = 'postgres';

    const DATA_BASE_USER   = 'root';

    const DATA_BASE_PASSWD = 'root_MANUVA1';

    const OPT = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getDataBase(): ?string
    {
        return $this->dataBase;
    }

    public function setDataBase(string $dataBase): self
    {
        $this->dataBase = $dataBase;

        return $this;
    }

    public function getDataBaseUser(): ?string
    {
        return $this->dataBaseUser;
    }

    public function setDataBaseUser(string $dataBaseUser): self
    {
        $this->dataBaseUser = $dataBaseUser;

        return $this;
    }

    public function getPasswd(): ?string
    {
        return $this->passwd;
    }

    public function setPasswd(string $passwd): self
    {
        $this->passwd = $passwd;

        return $this;
    }

    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    public function setDsn(string $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    public function getOpt(): array
    {
        return $this->opt;
    }

    public function setOpt(?array $opt): self
    {
        $this->opt = $opt;

        return $this;
    }
}
