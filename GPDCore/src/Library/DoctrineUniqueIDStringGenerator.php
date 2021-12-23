<?php
namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class DoctrineUniqueIDStringGenerator extends AbstractIdGenerator
{
    public function generate(EntityManager $em, $entity) {
        $prefix = rand()."__";
        return md5(uniqid($prefix, true));
    }
}