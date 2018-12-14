<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EaPaysites\Entity\AbstractEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 * @UniqueEntity("emailCanonical")
 */
class User extends AbstractEntity implements UserInterface, AdvancedUserInterface, \Serializable
{
    const BLOCK_TYPE_LOGIN_SHARING = 'login_sharing';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string|null
     * @Assert\Length(
     *      min = 2,
     *      max = 100
     * )
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = 100
     * )
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = 100
     * )
     * @Assert\Email(
     *     message = "{{ value }} is not a valid email.",
     *     strict=true,
     *     checkMX = true
     * )
     * @ORM\Column(name="email_canonical", type="string", length=100, unique=true, nullable=true)
     */
    private $emailCanonical;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $emailConfirmed = false;

    /**
     * @var PurchaseToken[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PurchaseToken", mappedBy="user", cascade={"persist"})
     */
    private $purchaseTokens;

    /**
     * @var ContentPermission[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ContentPermission", mappedBy="user", cascade={"persist"})
     */
    private $contentPermissions;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $blockType;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $blockedUntil;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 1})
     */
    private $active = true;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->purchaseTokens     = new ArrayCollection();
        $this->contentPermissions = new ArrayCollection();
        $this->setCreatedAtValue();
    }

    /**
     * @return int
     */
    public function getId()
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
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    /**
     * @param string|null $email
     */
    public function setEmailCanonical(?string $email)
    {
        $this->emailCanonical = $email;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->emailCanonical;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->emailCanonical,
                $this->password,
            ]
        );
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->emailCanonical,
            $this->password,
            ) = unserialize($serialized);
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed;
    }

    /**
     * @param bool $emailConfirmed
     */
    public function setEmailConfirmed(bool $emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;
    }

    /**
     * @return PurchaseToken[]|ArrayCollection
     */
    public function getPurchaseTokens()
    {
        return $this->purchaseTokens;
    }

    /**
     * @param PurchaseToken[]|ArrayCollection|iterable $purchaseTokens
     */
    public function setPurchaseTokens(iterable $purchaseTokens)
    {
        $this->purchaseTokens = $purchaseTokens;
    }

    /**
     * @param PurchaseToken $purchaseToken
     */
    public function addPurchaseToken(PurchaseToken $purchaseToken)
    {
        $this->purchaseTokens->add($purchaseToken);
    }

    /**
     * Get the first unused purchase token for the specific kind.
     *
     * @param string $kind
     *
     * @return PurchaseToken|null
     */
    public function getAvailablePurchaseToken(string $kind): ?PurchaseToken
    {
        if (count($this->purchaseTokens) === 0) {
            return null;
        }

        $availablePurchaseTokens = $this->purchaseTokens->filter(
            function (PurchaseToken $token) use ($kind) {
                return !$token->isUsed() && $token->getKind() == $kind;
            }
        );

        return $availablePurchaseTokens->first() ?: null;
    }

    /**
     * @return ContentPermission[]|ArrayCollection
     */
    public function getContentPermissions()
    {
        return $this->contentPermissions;
    }

    /**
     * @param iterable $contentPermissions
     */
    public function setContentPermissions(iterable $contentPermissions)
    {
        $this->contentPermissions = $contentPermissions;
    }

    /**
     * @param ContentPermission $contentPermission
     */
    public function addContentPermission(ContentPermission $contentPermission)
    {
        $this->contentPermissions->add($contentPermission);
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    public function isEqual($user): bool
    {
        return is_object($user) && $user instanceof User && $user->getId() == $this->getId();
    }

    /**
     * @return string
     */
    public function getBlockType(): ?string
    {
        return $this->blockType;
    }

    /**
     * @param string $blockType
     */
    public function setBlockType(string $blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return (bool)$this->blockType;
    }

    /**
     * @return \DateTime
     */
    public function getBlockedUntil(): ?\DateTime
    {
        return $this->blockedUntil;
    }

    /**
     * @param \DateTime $blockedUntil
     */
    public function setBlockedUntil(\DateTime $blockedUntil)
    {
        $this->blockedUntil = $blockedUntil;
    }

    /**
     * @param string    $blockType
     * @param \DateTime $blockedUntil
     */
    public function block(string $blockType, \DateTime $blockedUntil)
    {
        $this->setBlockType($blockType);
        $this->setBlockedUntil($blockedUntil);
    }

    public function unblock()
    {
        $this->blockType    = null;
        $this->blockedUntil = null;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return $this->active;
    }

    public function updateLastLogin()
    {
        $this->lastLogin = new \DateTime("now");
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }
}
