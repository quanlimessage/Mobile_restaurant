<?php

namespace App\Models;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Database\Eloquent\Model;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 * @ORM\HasLifecycleCallbacks()
 */
class Category extends Model {
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
	public $id;

    /**
     * @ORM\Column(type="string")
     */
    public $name_jp;
	
	/**
     * @ORM\Column(type="string")
     */
    public $name_en;
	
	/**
     * @ORM\Column(type="string")
     */
    public $name_vi;
	
	
	/**
     * @ORM\Column(type="string")
     */
    public $category_image;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    public $updated_at;

    public function __construct($_category) {
        $this->name_jp = !empty($_category->name_jp) ? $_category->name_jp : "";
        $this->name_en = !empty($_category->name_en) ? $_category->name_en : "";
        $this->name_vi = !empty($_category->name_vi) ? $_category->name_vi : "";
        $this->category_image = !empty($_category->category_image) ? $_category->category_image : "";
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    public function updateCategory($_category)
    {
        $this->name_jp = !empty($_category->name_jp) ? $_category->name_jp : "";
        $this->name_en = !empty($_category->name_en) ? $_category->name_en : "";
        $this->name_vi = !empty($_category->name_vi) ? $_category->name_vi : "";
        $this->category_image = !empty($_category->category_image) ? $_category->category_image : "";
    }
}
