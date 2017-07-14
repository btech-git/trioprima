<?php

namespace LibBundle\Twig;

class GridExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'grid_extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('grid_top', array($this, 'functionGridTop'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_bottom', array($this, 'functionGridBottom'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_search_panel', array($this, 'functionGridSearchPanel'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_sort_panel', array($this, 'functionGridSortPanel'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_page_panel', array($this, 'functionGridPagePanel'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_action', array($this, 'functionGridAction'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_panels', array($this, 'functionGridPanels'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_result_info', array($this, 'functionGridResultInfo'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_navigation', array($this, 'functionGridNavigation'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_info_navigation', array($this, 'functionGridInfoNavigation'), array('needs_environment' => true, 'needs_context' => true)),
            new \Twig_SimpleFunction('grid_script', array($this, 'functionGridScript'), array('needs_environment' => true, 'needs_context' => true)),
        );
    }

    public function functionGridTop(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_top', array('grid' => $grid));
    }

    public function functionGridBottom(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_bottom', array('grid' => $grid));
    }

    public function functionGridSearchPanel(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_search_panel', array('grid' => $grid));
    }

    public function functionGridSortPanel(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_sort_panel', array('grid' => $grid));
    }

    public function functionGridPagePanel(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_page_panel', array('grid' => $grid));
    }

    public function functionGridAction(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_action', array('grid' => $grid));
    }

    public function functionGridPanels(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_panels', array('grid' => $grid));
    }

    public function functionGridResultInfo(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_result_info', array('grid' => $grid));
    }

    public function functionGridNavigation(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_navigation', array('grid' => $grid));
    }

    public function functionGridInfoNavigation(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_info_navigation', array('grid' => $grid));
    }

    public function functionGridScript(\Twig_Environment $environment, $context, $grid)
    {
        $this->displayGrid($environment, $context, 'grid_script', array('grid' => $grid));
    }

    private function displayGrid(\Twig_Environment $environment, $context, $block, array $variables)
    {
        $template = $environment->loadTemplate($context['grid_layout']);
        $template->displayBlock($block, $variables);
    }
}
