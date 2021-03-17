<?php
namespace Xanweb\Foundation\Entity\Service;

/**
 * Interface ServiceInterface
 *
 * @template T
 */
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
     * @return T
     */
    public function createEntity();

    /**
     * Finds all entities in the repository.
     *
     * @return T[] the entities
     */
    public function getList();

    /**
     * Finds all entities in the repository sorted by given fields.
     *
     * @param array $orderBy
     *
     * @return T[] the entities
     */
    public function getSortedList($orderBy = []);

    /**
     * Finds the entity by its primary key / identifier.
     *
     * @param mixed $id The identifier
     *
     * @return T|null The entity instance or NULL if the entity can not be found
     */
    public function getByID($id);

    /**
     * Create Entity.
     *
     * @param array $data
     *
     * @return T
     */
    public function create(array $data = []);

    /**
     * Update Entity.
     *
     * @param T $entity
     *
     * @return bool
     */
    public function update($entity, array $data = []);

    /**
     * Persist a list of entities and flush all changes.
     *
     * @param T[] $entities
     */
    public function bulkSave(array $entities);

    /**
     * Delete Entity.
     *
     * @param T $entity
     *
     * @return bool
     */
    public function delete($entity);

    /**
     * Delete a list of entities and flush all changes.
     *
     * @param T[] $entities
     */
    public function bulkDelete(array $entities);
}
