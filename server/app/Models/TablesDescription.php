<?php

namespace App\Models;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Database\Eloquent\Model;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="tables_description")
 * @ORM\HasLifecycleCallbacks()
 */
class TablesDescription extends Model
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="table_description_id")
     */
    public $table_description_id;

    /**
     * @ORM\Column(type="string")
     */
    public $language_id;

    /**
     * @ORM\Column(type="integer")
     */
    public $table_id;

    /**
     * @ORM\Column(type="string")
     */
    public $table_name;

    /**
     * @ORM\Column(type="string")
     */
    public $table_description;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    public $updated_at;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Models\Tables", inversedBy="descriptions")
    * @ORM\JoinColumn(name="table_id", referencedColumnName="table_id")
    */
    protected $table;


    public function __construct($_table) {
        $this->language_id = !empty($_table['language_id']) ? $_table['language_id'] : "";
        $this->table_name = !empty($_table['table_name']) ? $_table['table_name'] : "";
        $this->table_description = !empty($_table['table_description']) ? $_table['table_description'] : "";
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    public function updateTableDescription($_table) {
        $this->language_id = !empty($_table['language_id']) ? $_table['language_id'] : "";
        $this->table_name = !empty($_table['table_name']) ? $_table['table_name'] : "";
        $this->table_description = !empty($_table['table_description']) ? $_table['table_description'] : "";
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getTables()
    {
        return $this->tables;
    }

}
