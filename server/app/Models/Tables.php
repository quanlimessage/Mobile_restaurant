<?php

namespace App\Models;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Database\Eloquent\Model;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="tables")
 * @ORM\HasLifecycleCallbacks()
 */
class Tables extends Model
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     */
    public $mac_address;

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
    public $description_jp;

    /**
     * @ORM\Column(type="string")
     */
    public $description_en;

    /**
     * @ORM\Column(type="string")
     */
    public $description_vi;

    /**
     * @ORM\Column(type="string")
     */
    public $table_status;

    /**
     * @ORM\Column(type="integer")
     */
    public $num_of_seat;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    public $updated_at;

    /**
    * @ORM\OneToMany(targetEntity="\App\Models\TablesDescription", mappedBy="tables")
    */
    protected $descriptions;

    public function __construct($_table) {
        $this->table_status = !empty($_table->table_status) ? $_table->table_status : "";
        $this->name_jp = !empty($_table->name_jp) ? $_table->name_jp : "";
        $this->name_en = !empty($_table->name_en) ? $_table->name_en : "";
        $this->name_vi = !empty($_table->name_vi) ? $_table->name_vi : "";
        $this->description_jp = !empty($_table->description_jp) ? $_table->description_jp : "";
        $this->description_en = !empty($_table->description_en) ? $_table->description_en : "";
        $this->description_vi = !empty($_table->description_vi) ? $_table->description_vi : "";
        $this->table_status = !empty($_table->table_status) ? $_table->table_status : "";
        $this->num_of_seat = !empty($_table->num_of_seat) ? $_table->num_of_seat : 0;
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }


    public function setDescriptions($descriptions)
    {
        $this->descriptions = $descriptions;
    }

    public function getDescriptions()
    {
        return $this->descriptions;
    }

    public function addDescriptions($description)
    {
        $this->descriptions[] = $description;
    }

    public function updateTable($_table)
    {
        $this->table_status = !empty($_table->table_status) ? $_table->table_status : "";
        $this->num_of_seat = !empty($_table->num_of_seat) ? $_table->num_of_seat : "";
    }

    public function getId()
    {
        return $this->id;
    }
}