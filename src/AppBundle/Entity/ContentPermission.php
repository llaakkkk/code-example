<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EaPaysites\Entity\AbstractEntity;

/**
 * @ORM\Table(name="content_permissions")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ContentPermissionRepository")
 */
class ContentPermission extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="contentPermissions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $contentId;

    /**
     * @var string
     * @ORM\Column(length=100)
     */
    private $contentType;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $grantedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @var string
     * @ORM\Column(length=50)
     */
    private $permission;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $grantedVia;

    /**
     * @var string
     * @ORM\Column(length=25)
     */
    private $grantedViaType;

    /**
     * @var string
     * @ORM\Column(length=100, nullable=true)
     */
    private $status;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getContentId(): int
    {
        return $this->contentId;
    }

    /**
     * @param int $contentId
     */
    public function setContentId(int $contentId)
    {
        $this->contentId = $contentId;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType(string $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return \DateTime
     */
    public function getGrantedAt(): \DateTime
    {
        return $this->grantedAt;
    }

    /**
     * @param \DateTime|string $grantedAt
     */
    public function setGrantedAt($grantedAt)
    {
        $grantedAt = $grantedAt instanceof \DateTime ? $grantedAt : new \DateTime($grantedAt);

        $this->grantedAt = $grantedAt;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime|string|null $expiresAt
     */
    public function setExpiresAt($expiresAt = null)
    {
        if ($expiresAt && !$expiresAt instanceof \DateTime) {
            $expiresAt = new \DateTime($expiresAt);
        }

        $this->expiresAt = $expiresAt;
    }

    /**
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * @param string $permission
     */
    public function setPermission(string $permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return string
     */
    public function getGrantedVia(): string
    {
        return $this->grantedVia;
    }

    /**
     * @param string $grantedVia
     */
    public function setGrantedVia(string $grantedVia)
    {
        $this->grantedVia = $grantedVia;
    }

    /**
     * @return string
     */
    public function getGrantedViaType(): string
    {
        return $this->grantedViaType;
    }

    /**
     * @param string $grantedViaType
     */
    public function setGrantedViaType(string $grantedViaType)
    {
        $this->grantedViaType = $grantedViaType;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->getExpiresAt() && time() > $this->getExpiresAt()->getTimestamp();
    }

    /**
     * @param string $permission
     *
     * @return bool
     */
    public function isAllowed(string $permission): bool
    {
        return $this->getPermission() == $permission;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status = null)
    {
        $this->status = $status;
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }
}
