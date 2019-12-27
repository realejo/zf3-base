# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.2.5] - 2019-12-27
### Fixed
- `MapperAbstract::setHydratorEntity()` had a wrong signature. Changed to `string`.


## [2.2.4] - 2019-11-19
### Added
- `Enum`/`EnumFlagged` have `setValue()` separated form `__construct()` to change the value without the need to recreate the object
- `EnumFlagged::add()` and `EnumFlagged::remove()` to change the value without the need to recreate the object

## [2.2.3] - 2019-11-11
### Added
- `Realejo\Db\DeleteWithLimit` to use with `Zend\Db\TableGateway::deleteWith()` and limit the records deleted (tested only on mysql)

## [2.2.2] - 2019-07-16
### Fixed
- Bug on cache when using `Service::findAssoc` with a different key than the actual table key

## [2.2.1] - 2019-07-16
### Changed
- `MailSender` lookup for configuration files in several folders.

### Deprecated
- `tableJoinLeft` at `MapperAbstract`. Should use `tableJoin`

## [2.2.0] - 2018-11-28
### Added
- Bump to 7.1
- Added `Realejo\Cache\CacheService`.
- Added `Realejo\Backup\BackupService`.
- Added `View\Helper\FrmEnumChecked*` option: `show-description`.
- Added `View\Helper\FrmEnumCheckbox*` option `show-description`, `read-only` and `required`.
- Added `View\Helper\FrmEnumChecknx*` option `required`.

### Changed
- `DateHelper::isDate()` validates only if it's a string.
- Removed `APPLICATION_DATA` dependency from `CacheService`.
- `ArrayObject::$jsonKeys` is separated in `ArrayObject::$jsonArrayKeys` and `ArrayObject::$jsonObjectKeys`.

### Deprecated
- `APPLICATION_DATA` at `CacheService`.

### Removed
- `Realejo\Utils\Cache`, use `Realejo\Cache\CacheService`.
- `Zend\Json` dependency

### Fixed
- Fixed cache key bug when using `Zend\Db\Select`.

## [2.1.6] - 2018-03-20
### Fixed
- Warning prevention when there are no metadata created in the database and method `MetadataService::removeMetadata()` is called.

## [2.1.5] - 2018-03-12
### Changed
- `ServiceLocatorTrait::getFromServiceLocator()`: Throwing exception if no service locator is defined.

## [2.1.4] - 2018-03-05
### Fixed
- `EnumFlagged::isValid()`: bug when calculating max valid value.

## [2.1.3] - 2018-03-02
### Added
- `ArrayObject`: now it populates enum values.

## [2.1.2] - 2018-02-28
### Added
- `Enum::getValues()` returns an array with the constant values.

## [2.1.1] - 2018-02-27
### Changed
- Fixed `getValueName()` and `getValueDescriptio()` methods in Enum` and `EnumFlagged`; 
- `__invoke()` method in view helpers `frmEnumChecked`, `frmEnumCheckbox` and `frmEnumSelect`. 

## [2.1.0] - 2018-02-27
### Added
- `Enum` and `EnumFlagged`;
- View helpers `frmEnumChecked` to display the chosen options. 
`frmEnumCheckbox` and `frmEnumSelect` to use in forms. 
### Removed
- drop support for PHP 5.6. 

## [2.0.2] - 2018-02-21
### Added
- View helpers for `formatDate`, `CKEditor` and `text`.

## [2.0.1] - 2018-02-21
### Added
- This CHANGELOG file to hopefully serve as an evolving example of a
  standardized open source project CHANGELOG.

## [2.0.0] - 2018-02-20
### Added
- View helpers for `getInputFilter`, `ApplicationConfig` and `formValidation`.  
  
## [1.1.5] - 2018-02-02
### Changed
- Method `getCacheInternalId` overridden  to fix a bug in Zend Paginator based on DbSelect Adapter.
