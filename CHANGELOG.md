# Changelog

# 1.1.0

- New: Added HTTP cache headers for cached files
- New: Added configuration from _ss_environment.php file constants
- New: Added quiet mode to rebuild cache task
- New: Added cache miss header when skipping cache
- Bugfix: Make sure URLs that get cached are always part of `allPagesToCache`
- Bugfix: `Config` not loading properly for `RsyncMultiHostPublisher`
- Bugfix: `publishPages` must use `Live` stage
- Bugfix: Don't ignore `$cacheBaseName` for cache files

# 1.0.0

- Initial release
