<?php
/*
This widget is a heavily modified version of the two github repositories listed below:

https://github.com/emizentech/magento2-category-list-widget
https://github.com/Sebwite/magento2-category-sidebar/blob/master/view/frontend/templates/sidebar.phtml
*/
\Magento\Framework\Component\ComponentRegistrar::register(
	\Magento\Framework\Component\ComponentRegistrar::MODULE,
	'PlymDesign_CategoryWidget',
	__DIR__
);