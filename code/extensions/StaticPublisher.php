<?php
/**
 * @package staticpublisher
 */
abstract class StaticPublisher extends DataExtension {

	/**
	 * Defines whether to output information about publishing or not. By 
	 * default, this is off, and should be turned on when you want debugging 
	 * (for example, in a cron task).
	 *
	 * @var boolean
	 *
	 * @config
	 */
	private static $echo_progress = false;
	
	/**
	 * Realtime static publishing... the second a page is saved, it is 
	 * written to the cache.
	 *
	 * @var boolean
	 *
	 * @config
	 */
	private static $disable_realtime = false;
	
	/**
	 * This is the current static publishing theme, which can be set at any 
	 * point. If it's not set, then the last non-null theme, set via 
	 * SSViewer::set_theme() is used. The obvious place to set this is in 
	 * _config.php
	 *
	 * @var string
	 *
	 * @config
	 */
	private static $static_publisher_theme = false;
	
	/**
	 * @var boolean includes a timestamp at the bottom of the generated HTML 
	 * of each file, which can be useful for debugging issues with stale 
	 * caches etc.
	 *
	 * @config
	 */
	private static $include_caching_metadata = false;

	/**
	 * @param array
	 */
	abstract function publishPages($pages);

	/**
	 * @param array
	 */
	abstract function unpublishPages($pages);

	/**
	 * @deprecated
	 * @param string
	 */
	public static function set_static_publisher_theme($theme) {
		Deprecation::notice('1.0', 'Use the new config system. SSViewer.static_publisher_theme');

		Config::inst()->update('StaticPublisher', 'static_publisher_theme', $theme);
	}
	
	/**
	 * @deprecated
	 *
	 * @return string
	 */
	public static function static_publisher_theme() {
		Deprecation::notice('1.0', 'Use the new config system. SSViewer.static_publisher_theme');

		return Config::inst()->get('StaticPublisher', 'static_publisher_theme');
	}

	/**
	 * @deprecated
	 *
	 * @return boolean
	 */
	public static function echo_progress() {
		Deprecation::notice('1.0', 'Use the new config system. SSViewer.static_publisher_theme');

		return Config::inst()->get('StaticPublisher', 'echo_progress');
	}
	
	/**
	 * @deprecated
	 *
	 */
	public static function set_echo_progress($progress) {
		Deprecation::notice('1.0', 'Use the new config system. SSViewer.static_publisher_theme');

		Config::inst()->get('StaticPublisher', 'echo_progress', $progress);
	}

	/**
	 * Called after a page is published.
	 *
	 * @param SiteTree
	 */
	public function onAfterPublish($original) {
		$this->republish($original);
	}
	
	/**
	 * Called after link assets have been renamed, and the live site has been 
	 * updated, without an actual publish event.
	 * 
	 * Only called if the published content exists and has been modified.
	 */
	public function onRenameLinkedAsset($original) {
		$this->republish($original);
	}
	
	public function republish($original) {
		if (Config::inst()->get('StaticPublisher', 'disable_realtime')) {
			return;
		}

		$urls = array();
		
		if($this->owner->hasMethod('pagesAffectedByChanges')) {
			$urls = $this->owner->pagesAffectedByChanges($original);
		} else {
			$pages = Versioned::get_by_stage('SiteTree', 'Live', '', '', '', 10);
			if($pages) {
				foreach($pages as $page) {
					$urls[] = $page->AbsoluteLink();
				}
			}
		}
		
		// Note: Similiar to RebuildStaticCacheTask->rebuildCache()
		foreach($urls as $i => $url) {
			if(!is_string($url)) {
				user_error("Bad URL: " . var_export($url, true), E_USER_WARNING);
				continue;
			}

			// Remove leading slashes from all URLs (apart from the homepage)
			if(substr($url,-1) == '/' && $url != '/') $url = substr($url,0,-1);
			
			$urls[$i] = $url;
		}

		$urls = array_unique($urls);

		$this->publishPages($urls);
	}
	
	/**
	 * Get changes and hook into underlying functionality.
	 */
	public function onAfterUnpublish($page) {
		if (Config::inst()->get('StaticPublisher', 'disable_realtime')) {
			return;
		}
		
		// Get the affected URLs
		if($this->owner->hasMethod('pagesAffectedByUnpublishing')) {
			$urls = $this->owner->pagesAffectedByUnpublishing();
			$urls = array_unique($urls);
		} else {
			$urls = array($this->owner->AbsoluteLink());
		}
		
		$legalPages = singleton('Page')->allPagesToCache();
		
		$urlsToRepublish = array_intersect($urls, $legalPages);
		$urlsToUnpublish = array_diff($urls, $legalPages);

		$this->unpublishPages($urlsToUnpublish);
		$this->publishPages($urlsToRepublish);
	}
		
	/**
	 * 
	 * @param string $url
	 * @return array
	 */
	public function getMetadata($url) {
		return array(
			'Cache generated on ' . date('Y-m-d H:i:s T (O)')
		);
	}
}