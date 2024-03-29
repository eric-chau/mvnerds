<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace MVNerds\PropelBundle\Controller;

use Propel\PropelBundle\Controller\PanelController as BasePanelController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@pixel-cookers.com>
 */
class PanelController extends BasePanelController
{
    /**
     * Renders the profiler panel for the given token.
     *
     * @param string $token The profiler token
     * @param string $connection The connection name
     * @param integer $query
     *
     * @return Symfony\Component\HttpFoundation\Response A Response instance
     */
    public function explainAction($token, $connection, $query)
    {
        $profiler = $this->container->get('profiler');
        $profiler->disable();

        $profile = $profiler->loadProfile($token);
        $queries = $profile->getCollector('propel')->getQueries();
		
        if (!isset($queries[$query])) {
            return new Response('This query does not exist.');
        }

        // Open the connection
        $con = \Propel::getConnection($connection);

        // Get the adapter
        $db = \Propel::getDB($connection);

        try {
            $stmt = $db->doExplainPlan($con, $queries[$query]['sql']);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return new Response('<div class="error">This query cannot be explained.</div>');
        }
		
		//var_dump($queries[$query]); die;
		
        return $this->container->get('templating')->renderResponse(
            'MVNerdsPropelBundle:Panel:explain.html.twig',
            array(
                'data'			=> $results,
                'query'			=> $query,
				'stacktrace'	=> $queries[$query]['stacktrace']
            )
        );
    }
}
