<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SpecificationVersions Model
 *
 * @property \App\Model\Table\SpecificationsTable|\Cake\ORM\Association\BelongsTo $Specifications
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\SpecificationVersion get($primaryKey, $options = [])
 * @method \App\Model\Entity\SpecificationVersion newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SpecificationVersion[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SpecificationVersion|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SpecificationVersion patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SpecificationVersion[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SpecificationVersion findOrCreate($search, callable $callback = null, $options = [])
 */
class SpecificationVersionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('specification_versions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Specifications', [
            'foreignKey' => 'specification_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('file_name')
            ->requirePresence('file_name', 'create')
            ->notEmpty('file_name');

        $validator
            ->date('created_date')
            ->requirePresence('created_date', 'create')
            ->notEmpty('created_date');

        $validator
            ->scalar('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['specification_id'], 'Specifications'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
