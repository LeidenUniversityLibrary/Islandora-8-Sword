# Islandora Instant Importer SWORD Activator

## Introduction

This module provides the SWORD v1.3 API.

## Configuration

When configuring the SWORD v1 fill out the following:
* Import user name: when a POST is done (creating a resource), this user's name and password should be used for basic authentication. If valid, this user is used as the one doing the import.
* Base URL path: the base URL path for the SWORD api. Do not use a leading slash.
* Service path: the path for the service document.
* Collection name: this module does not use or care about any collections that already exist in Islandora. This name of a collection is not linked to any existing collection and can be anything.
* Accepted MIME type: the MIME type(s) accepted by this Islandora Instant Importer. If the MIME type of the POSTed data is not included, then the POST will fail.
* Accepted packaging: The URI(s) of the packaging format accepted by this Islandora Instant Importer. The URI(s) can be followed by a quality value to indicate preference. If the packaging URI of the POSTed data (X-Packaging request header) is not included, then the POST will fail.

## Implementation details

This module implements a basic SWORD v1.3 interface.
The returned service document only contains a single collection and the workspace name is the same as the name of the Islandora Instant Importer.
Editing, retrieving or deleting resources is not implemented (yet), as it is not mandatory.
Also is the X-Verbose header not supported. The X-No-Op is available, so a dry run is available.

## Maintainers/Sponsors

Current maintainers:

* [Rama Mwinyimbegu](https://github.com/ublrama)

## Development

If you would like to contribute to this module, please contact the current maintainer.

