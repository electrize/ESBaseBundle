<?php

namespace ES\Bundle\BaseBundle\Form\Type;

use ES\Bundle\BaseBundle\Assets\AssetsStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\Asset\PackageInterface;

class SummerNoteType extends AbstractType
{
	/**
	 * @var AssetsStack
	 */
	private $assetsStack;

	/**
	 * @var PackageInterface
	 */
	private $assetsHelper;

	static private $assetsIncluded = false;

	/**
	 * @var RouterInterface
	 */
	private $router;

	private $lang;

	public function __construct(AssetsStack $assetsStack, PackageInterface $assetsHelper, RouterInterface $router, Request $request)
	{
		$this->assetsStack  = $assetsStack;
		$this->assetsHelper = $assetsHelper;
		$this->router       = $router;

		$locale     = $request->getLocale();
		$this->lang = $locale . '-' . strtoupper($locale);

		if (!self::$assetsIncluded) {
			self::$assetsIncluded = true;
			$this->assetsStack->appendCSSInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/summernote/summernote.css'));
			$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/summernote/summernote.min.js'));
			$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/summernote/locales/summernote-' . $this->lang . '.js'));
		}
	}

	/**
	 * Add the file_path option
	 *
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'image_upload_url'     => $this->router->generate('es_file_upload_file_upload'),
			'filter_name'          => 'wysiwyg',
			'image_upload_loading' => 'form.summernote.image_upload.loading',
			'required'             => false,
			'lang'                 => $this->lang,
			'toolbar'              => array(
				'style'    => array('style'),
				'font'     => array('bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough',
					'clear'),
				'fontname' => array('fontname'),
				'color'    => array('color'),
				'para'     => array('ul', 'ol', 'paragraph'),
				'height'   => array('height'),
				'table'    => array('table'),
				'insert'   => array('link', 'picture', 'video', 'hr'),
				'view'     => array('fullscreen', 'codeview'),
				'help'     => array('help')
			)
		));
	}

	/**
	 * Pass the file URL to the view
	 *
	 * @param FormView      $view
	 * @param FormInterface $form
	 * @param array         $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$rootForm = $form->getRoot();
		$token    = (string)$rootForm->getConfig()->getOption('csrf_token_manager')->getToken('file');

		$jsOptions = [
			'formData'              => [
				'file[_token]' => $token,
				'filter_name'  => $options['filter_name'],
			],
			'imageUploadUrl'        => $options['image_upload_url'],
			'imageUploadLoadingTxt' => $options['image_upload_loading'],
			'lang'                  => $options['lang'],
		];

		$view->vars['js_options'] = json_encode($jsOptions);

		$view->vars['toolbar'] = $options['toolbar'];
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'es_summernote';
	}

	public function getParent()
	{
		return 'textarea';
	}
}