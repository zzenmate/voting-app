<?php

namespace AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vote Controller
 *
 * @Rest\NamePrefix("api_vote_")
 * @Rest\Prefix("/v1/votes")
 */
class VoteController extends FOSRestController
{
    use ControllerHelperTrait;

    /**
     * Повертає колекцію результатів голосувань
     *
     * @ApiDoc(
     *     description="Список достпних голосувань",
     *     section="Vote",
     *     statusCodes={
     *          200="Returned when successful",
     *          500="Returned when internal error on the server occurred"
     *      }
     * )
     *
     * @param ParamFetcherInterface $paramFetcher Param fetcher
     *
     * @return Response
     *
     * @Rest\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true, description="Limit", default="10")
     * @Rest\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true, description="Offset", default="0")
     * @Rest\QueryParam(name="session", requirements="\d+", nullable=true, description="Фільтр по ID сессії")
     * @Rest\QueryParam(name="date", nullable=true, description="Фільтр по даті сессії(>, <, =). Приклад: (?date=>2016-12-15), (?date==2016-12-15)")
     *
     * @Rest\Get("")
     */
    public function getListAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $filters = $paramFetcher->all();
            $repository = $this->getDoctrine()->getRepository('AppBundle:Vote');
            $matchingService = $this->get('app.matching');

            if (empty($filters['date'])) {
                $votes = $matchingService->matching($repository, $filters);
            } else {
                list($operator, $value) = $matchingService->getOperatorAndValueFromFilter($filters['date']);
                $votes = $repository->findVotesBySessionDate(new \DateTime($value), $operator, $filters['_limit'], $filters['_offset']);
            }

            $view = $this->createViewForHttpOkResponse([
                '_metadata' => [
                    'total' => count($votes),
                    '_limit' => (int) $filters['_limit'],
                    '_offset' => (int) $filters['_offset'],
                ],
                'sessions' => $votes,
            ]);
        } catch (\Exception $e) {
            throw $this->createInternalServerErrorException();
        }

        return $this->handleView($view);
    }
}
