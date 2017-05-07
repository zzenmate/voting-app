<?php

namespace AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Controller
 *
 * @Rest\NamePrefix("api_session_")
 * @Rest\Prefix("/v1/sessions")
 */
class SessionController extends FOSRestController
{
    use ControllerHelperTrait;

    /**
     * Повертає колекцію сессій міської ради
     *
     * @ApiDoc(
     *     description="Список достпних сессій",
     *     section="Session",
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
     * @Rest\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true, description="Limit", default="20")
     * @Rest\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true, description="Offset", default="0")
     * @Rest\QueryParam(name="date", nullable=true, description="Фільтр по даті(>, <, =). Приклад: (?date=>2016-12-15), (?date==2016-12-15)")
     *
     * @Rest\Get("")
     */
    public function getListAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $filters = $paramFetcher->all();
            $repository = $this->getDoctrine()->getRepository('AppBundle:Session');
            $sessions = $this->get('app.matching')->matching($repository, $filters);

            $view = $this->createViewForHttpOkResponse([
                '_metadata' => [
                    'total' => count($sessions),
                    '_limit' => (int) $filters['_limit'],
                    '_offset' => (int) $filters['_offset'],
                ],
                'sessions' => $sessions,
            ]);
        } catch (\Exception $e) {
            throw $this->createInternalServerErrorException();
        }

        return $this->handleView($view);
    }
}
