<?php

namespace App\Models;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Database\Eloquent\Model;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="foods")
 * @ORM\HasLifecycleCallbacks()
 */
class Foods extends Model {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
	public  $id;

    /**
     * @ORM\Column(type="string", name="name_jp")
     */
    public  $name_jp;

    /**
     * @ORM\Column(type="string", name="name_en")
     */
    public  $name_en;

    /**
     * @ORM\Column(type="string", name="name_vi")
     */
    public  $name_vi;

     /**
     * @ORM\Column(type="string", name="description_jp")
     */
    public  $description_jp;

     /**
     * @ORM\Column(type="string", name="description_en")
     */
    public  $description_en;

     /**
     * @ORM\Column(type="string", name="description_vi")
     */
    public  $description_vi;

    /**
     * @ORM\Column(type="integer", name="cost_price")
     */
    public  $cost_price;

    /**
     * @ORM\Column(type="integer")
     */
    public  $sale_price;

    /**
     * @ORM\Column(type="integer")
     */
    public  $time_to_prepare;

    /**
     * @ORM\Column(type="integer")
     */
    public  $category_id;

    /**
     * @ORM\Column(type="string")
     */
    public  $image_url;

    /**
     * @ORM\Column(type="datetime")
     */
    public  $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    public  $updated_at;

    public function __construct($_foods) {
        $this->name_jp = !empty($_foods->name_jp) ? $_foods->name_jp : "";
        $this->name_en = !empty($_foods->name_en) ? $_foods->name_en : "";
        $this->name_vi = !empty($_foods->name_vi) ? $_foods->name_vi : "";
        $this->description_jp = !empty($_foods->description_jp) ? $_foods->description_jp : "";
        $this->description_en = !empty($_foods->description_en) ? $_foods->description_en : "";
        $this->description_vi = !empty($_foods->description_vi) ? $_foods->description_vi : "";
        $this->cost_price = !empty($_foods->cost_price) ? $_foods->cost_price : "";
        $this->sale_price = !empty($_foods->sale_price) ? $_foods->sale_price : 0;
        $this->time_to_prepare = !empty($_foods->time_to_prepare) ? $_foods->time_to_prepare : 0;
        $this->category_id = !empty($_foods->category_id) ? $_foods->category_id : 0;
        $this->image_url = !empty($_foods->image_url) ? $_foods->image_url : "";
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    public function updateFood($_foods)
    {
        $this->name = !empty($_table->name) ? $_table->name : "";
        $this->price = !empty($_table->price) ? $_table->price : "";
    }
}
