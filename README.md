XenForo-FAQ-Manager
===================

XenForo add-on for Frequently Asked Questions management.

Features
------------
* Full FAQ with adding, editing and deleting
* Question categories
* BBCode supported answer fields
* Permissions for managing questions, and for managing categories
* Custom BBCode for linking to specific FAQ entries: `[faq=10][/faq]`
* (Option) Multiple sort options (alphabetical, submit date, view count)
* (Option) Show answers on the same page (slide down), or a new page
* (Option) Questions per page
* (Option) Social media share buttons enabled per question
* (Block) Most popular questions
* (Block) Recently added questions
* (Block) FAQ Statistics
* RSS feed for most recently added questions
* Uses XenForo phrases for easy language translations

Demo
------------

[http://shadowlack.com/faq/](http://shadowlack.com/faq/)

Installation
------------

1. Install using Chris Deeming's [Add-On Installer](http://xenforo.com/community/resources/add-on-installer.960/).

Or:

1. Upload files/directories underneath `upload` to your XenForo's root.
2. Import the add-on XML using the add-on importer in your Admin CP.

Configuration
------------

1. Set up the FAQ user group permissions.
2. Start adding categories and questions to your FAQ.
3. (Optional) Configure the add-on: `Home > Options > [Iversia] FAQ Manager`.

Requirements
------------
* [XenForo](http://xenforo.com/) 1.1.3

Copyright / License
------------

This project is released underneath the [DBAD PUBLIC LICENSE](http://www.dbad-license.org) by Phil Sturgeon.

Frequently Asked Questions
------------
**I have questions added, but the [faq] BB Code says question not found.**

To reduce retrieval duplication, questions are cached before they can be used in the [faq] BB Code. The cache is updated once an hour. If you would like to force a cache update, please go to `Tools > Cron Entries` in your Admin CP and manually run the `[Iversia] FAQ Update BB Code Cache` cron.

**The FAQ statistics are not updating!**

FAQ Statistics are updated once a day via a cron. You may run this manuallu by going to `Tools > Cron Entries` in your Admin CP and running the `[Iversia] FAQ Update Statistics` cron.