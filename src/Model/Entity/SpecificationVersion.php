<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SpecificationVersion Entity
 *
 * @property int $id
 * @property int $specification_id
 * @property int $user_id
 * @property string $file_name
 * @property \Cake\I18n\FrozenDate $created_date
 * @property string $status
 *
 * @property \App\Model\Entity\Specification $specification
 * @property \App\Model\Entity\User $user
 */
class SpecificationVersion extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
