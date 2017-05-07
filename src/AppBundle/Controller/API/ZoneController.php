<?php

namespace AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zone Controller
 *
 * @Rest\NamePrefix("api_zone_")
 * @Rest\Prefix("/v1/zones")
 */
class ZoneController extends FOSRestController
{
    use ControllerHelperTrait;

    const FIRST_NUMBER_DEPUTY = 1;
    const LAST_NUMBER_DEPUTY = 37;

    /**
     * Повертає колекцію зон впливу
     *
     * * count_votes_deputy - загальна кількість проведених голосувань з результатом: за, проти, утримався
     * * deputies[count_identical_votes] - загальна кількість ідентичних результатів голосувань, до результатів голосувань обраного депутата.
     * * impact_coefficient - коефіцієнт схожості голосувань
     *
     * @ApiDoc(
     *     description="Список зон впливу",
     *     section="Zone",
     *     statusCodes={
     *          200="Returned when successful",
     *          500="Returned when internal error on the server occurred"
     *      },
     * )
     *
     * @param int $id ID of deputy
     *
     * @return Response
     *
     * @Rest\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true, description="Limit of deputy", default="5")
     *
     * @Rest\Get("/{id}")
     */
    public function getListAction($id, ParamFetcherInterface $paramFetcher)
    {
        if ($id < self::FIRST_NUMBER_DEPUTY || $id > self::LAST_NUMBER_DEPUTY) {
            $view = $this->createViewForHttpNotFoundResponse([
                'message' => 'Deputy no found',
            ]);

            return $this->handleView($view);
        }

        $zoneService = $this->get('app.zone');

        $limit = (int) $paramFetcher->get('_limit');
        $countIdenticalVotesByDeputy = $zoneService->getCountIdenticalVotesByDeputy($id, $limit);
        $countVotesByDeputy = $zoneService->getTotalCountVotesByDeputy($id);

        $view = $this->view([
            '_metadata' => [
                'total' => count($countIdenticalVotesByDeputy),
                '_limit' => $limit,
            ],
            'count_votes_deputy' => $countVotesByDeputy,
            'deputies' => $countIdenticalVotesByDeputy,
        ]);

        return $this->handleView($view);
    }
}
