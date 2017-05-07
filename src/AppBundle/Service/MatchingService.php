<?php

namespace AppBundle\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Matching Service
 */
class MatchingService
{
    /** @var EntityManager $em */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Matching
     *
     * @param EntityRepository $repository
     * @param array            $fields
     *
     * @return array
     */
    public function matching(EntityRepository $repository, array $fields = [])
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata($repository->getClassName());

        $fields = array_filter($fields, function ($value) {
            return !empty($value);
        });

        $sort = null;
        $offset = null;
        $limit = null;

        if (isset($fields['_offset'])) {
            $offset = $fields['_offset'];
            unset($fields['_offset']);
        }

        if (isset($fields['_limit'])) {
            $limit = $fields['_limit'];
            unset($fields['_limit']);
        }

        $criteria = new Criteria(null, $sort, $offset, $limit);
        $expr = $criteria->expr();

        foreach ($fields as $field => $value) {
            if ($classMetadata->hasField($field)) {
                $comparison = $this->getComparison($classMetadata, $field, $value);
                $criteria->andWhere($comparison);
            } elseif ($classMetadata->hasAssociation($field)) {
                $className = $classMetadata->getAssociationTargetClass($field);
                $entity = $this->em->find($className, $value);

                $criteria->andWhere($expr->eq($field, $entity));
            }
        }

        return $repository->matching($criteria)->toArray();
    }

    /**
     * @param string $filter Filter
     *
     * @return array
     */
    public function getOperatorAndValueFromFilter($filter)
    {
        preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $filter, $matches);

        return [$matches[1], $matches[2]];
    }

    /**
     * @param ClassMetadata $classMetadata Class metadata
     * @param string        $field         Field
     * @param string|array  $value         Value
     *
     * @return Comparison
     */
    protected function getComparison(ClassMetadata $classMetadata, $field, $value)
    {
        list($operator, $value) = $this->separateOperator($value);

        $typeFiled = $classMetadata->getTypeOfField($field);
        if ($typeFiled == Type::DATETIME) {
            $comparison = new Comparison($field, $operator, new Value(new \DateTime($value)));
        } else {
            $comparison = new Comparison($field, $operator, new Value($value));
        }

        return $comparison;
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function separateOperator($value)
    {
        $operators = [
            '<>' => Comparison::NEQ,
            '<=' => Comparison::LTE,
            '>=' => Comparison::GTE,
            '<' => Comparison::LT,
            '>' => Comparison::GT,
            '=' => Comparison::EQ,
        ];

        if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $value, $matches)) {
            $operator = isset($operators[$matches[1]]) ? $operators[$matches[1]] : Comparison::CONTAINS;
            $value = $matches[2];
        } else {
            $operator = Comparison::CONTAINS;
        }

        return [$operator, $value];
    }
}
