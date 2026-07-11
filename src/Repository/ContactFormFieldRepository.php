<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Repository;

use c975L\ContactFormBundle\Entity\ContactFormField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactFormField>
 *
 * @method ContactFormField|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactFormField|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactFormField[]    findAll()
 * @method ContactFormField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactFormFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactFormField::class);
    }

    // Returns fields in display order, used to build both the dynamic form and the notification email
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}
