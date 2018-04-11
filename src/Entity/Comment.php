<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CommentFile", mappedBy="comment", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $files;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;
    public function __construct()
    {
        $this->files = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getComment(): ?string
    {
        return $this->comment;
    }
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
    public function getAuthor(): ?User
    {
        return $this->author;
    }
    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }
    public function setFiles(array $files){
        
        $this->files = new ArrayCollection();
        foreach ($files as $file) {
            $this->addFile($file);
        }
        return $this;
    }
    /**
     * @return Collection|CommentFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }
    public function addFile(CommentFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setComment($this);
        }
        return $this;
    }
    public function removeFile(CommentFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getComment() === $this) {
                $file->setComment(null);
            }
        }
        return $this;
    }
    public function getProduct(): ?CraftProduct
    {
        return $this->product;
    }
    public function setProduct(?CraftProduct $product): self
    {
        $this->product = $product;
        return $this;
    }
    /**
     * @Assert\Callback()
     * /
     * @param ExecutionContextInterface $context
     */
    public function validateComment(ExecutionContextInterface $context)
    {
        if (empty($this->files) && empty($this->comment)){
            
            $context->buildViolation('This field cannot be empty')
                ->at<path('comment')
                ->addViolation();
        }
    }
    
  
}