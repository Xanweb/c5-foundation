<?php
namespace Xanweb\Foundation\Entity\Service;

interface ServiceInterface
{
    /**
     * Get full entity class name.
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * Create New Entity instance.
     *
     * @return \Xanweb\Foundation\ConcreteObject Entity object
     */
    public function createEntity();

    /**
     * Finds all entities in the repository.
     *
     * @return \Xanweb\Foundation\ConcreteObject[] the entities
     */
    public function getList();

    /**
     * Finds all entities in the repository sorted by given fields.
     *
     * @param array $orderBy
     *
     * @return \Xanweb\Foundation\ConcreteObject[] the entities
     */
    public function getSortedList($orderBy = []);

    /**
     * Finds the entity by its primary key / identifier.
     *
     * @param mixed    $id          The identifier
     *
     * @return \Xanweb\Foundation\ConcreteObject|null The entity instance or NULL if the entity can not be found
     */
    public function getByID($id);

    /**
     * Create Entity.
     *
     * @return \Xanweb\Foundation\ConcreteObject
     */
    public function create(array $data = []);

    /**
     * Update Entity.
     *
     * @param \Xanweb\Foundation\ConcreteObject $entity
     *
     * @return bool
     */
    public function update($entity, array $data = []);

    /**
     * Persist a list of entities and flush all changes.
     */
    public function bulkSave(array $entities);

    /**
     * Delete Entity.
     *
     * @param \Xanweb\Foundation\ConcreteObject $entity
     *
     * @return bool
     */
    public function delete($entity);

    /**
     * Delete a list of entities and flush all changes.
     */
    public function bulkDelete(array $entities);
}