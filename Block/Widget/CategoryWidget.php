<?php
/*
This widget is a heavily modified version of the two github repositories listed below:

https://github.com/emizentech/magento2-category-list-widget
https://github.com/Sebwite/magento2-category-sidebar/blob/master/view/frontend/templates/sidebar.phtml
*/

namespace PlymDesign\CategoryWidget\Block\Widget;

class CategoryWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

	protected $_template = 'widget/categorywidget.phtml';

	const DEFAULT_IMAGE_WIDTH = 200;
	const DEFAULT_IMAGE_HEIGHT = 200;

	protected $coreRegistry;
	protected $categoryHelper;
	protected $categoryFactory;
	protected $categoryRepository;
	protected $storeManager;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Helper\Category $categoryHelper,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\CategoryRepository $categoryRepository,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	){
		$this->coreRegistry = $registry;
		$this->categoryHelper = $categoryHelper;
		$this->categoryFactory = $categoryFactory;
		$this->categoryRepository = $categoryRepository;
		$this->storeManager = $storeManager;
		parent::__construct($context);
	}

	public function getCategoryHelper()
	{
		return $this->categoryHelper;
	}

	public function getCategorymodel($id)
	{
		$_category = $this->categoryFactory->create();
		$_category->load($id);
		return $_category;
	}

	public function getCategoryCollection()
	{
		$category = $this->categoryFactory->create();

		if($this->getData('parentcat') > 0){
			$rootCat = $this->getData('parentcat');
			$category->load($rootCat);
		}else{
			$rootCat = $this->storeManager->getStore()->getRootCategoryId();
			$category->load($rootCat);
		}

		$storecats = $category->getChildrenCategories();

		return $storecats;
	}

	public function getChildCategories($category)
	{
		$childCategoryObj = $this->categoryRepository->get($category->getId());
        $childSubcategories = $childCategoryObj->getChildrenCategories();
		return $childSubcategories;
	}

	public function getChildCategoryView($category, $html = '')
	{
		$childCategories = $this->getChildCategories($category);

		if(count($childCategories) > 0){
			$html .= '<ul class="category-widget-list"' . ($this->isActive($category) ? ' style="display: block;"' : ' style="display: none;"') . '>';

			foreach($childCategories as $childCategory){
				$cat = $this->getCategorymodel($childCategory->getId());
				$html .= '<li class="category-widget-category' . ($this->isActive($childCategory) ? ' category-widget-category-active' : '') . '">';
				$html .= '<a href="' . $cat->getUrl() . '" class="' . ($this->isActive($childCategory) ? ' category-widget-category-active' : '') . '">';

				if($this->canShowImage() && $cat->getImageUrl()){
					$html .= '<img src="' . $cat->getImageUrl() . '" alt="" width="' . $this->getImagewidth() . '" height="' . $this->getImageheight() . '"/>';
				}else{
					$html .= '<span class="category-widget-category-title">' . $cat->getName() . '</span>';
				}

				$html .= '</a>';

				if($childCategory->hasChildren()){
					if($this->isActive($childCategory)){
						$html .= '<span class="category-widget-expanded"><i class="fa fa-minus-square-o"></i></span>';
					}else{
						$html .= '<span class="category-widget-expand"><i class="fa fa-plus-square-o"></i></span>';
					}
					$html .= $this->getChildCategoryView($childCategory, '');
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
		}

		return $html;
	}

	public function isActive($category)
	{
		$activeCategory = $this->coreRegistry->registry('current_category');
		$activeProduct  = $this->coreRegistry->registry('current_product');

		if(!$activeCategory){
			if($activeProduct !== null){
				return in_array($category->getId(), $activeProduct->getCategoryIds());
			}else{
				return false;
			}
		}

		if($category->getId() == $activeCategory->getId()){
			return true;
		}

		$childrenIds = $category->getAllChildren(true);

		if(!is_null($childrenIds) AND in_array($activeCategory->getId(), $childrenIds)){
			return true;
		}

		return ($category->getName() == $activeCategory->getName());
	}

	public function getImagewidth()
	{
		if($this->getData('imagewidth') == ''){
			return DEFAULT_IMAGE_WIDTH;
		}

		return (int)$this->getData('imagewidth');
	}

	public function getImageheight()
	{
		if($this->getData('imageheight') == ''){
			return DEFAULT_IMAGE_HEIGHT;
		}

		return (int)$this->getData('imageheight');
	}

	public function canShowImage()
	{
		if($this->getData('image') == 'image'){
			return true;
		}elseif($this->getData('image') == 'no-image'){
			return false;
		}
	}
}