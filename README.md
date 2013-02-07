XenForo-FAQ-Manager
===================

XenForo add-on for Frequently Asked Questions management.

Features
------------
* Full FAQ with adding, editing and deleting
* Question categories
* BBCode supported answer fields
* Custom BBCode for linking to specific FAQ entries: [faq=10]
* [Option] Multiple sort options (alphabetical, submit date, view count)
* [Option] Show answers on the same page (slide down), or a new page
* [Option] Questions per page
* [Block] Most popular questions
* [Block] Recently added questions
* RSS feed for most recently added questions
* Uses XenForo phrases for easy language translations

Installation
------------
To install:

1. Upload files/directories underneath `Upload` to your XenForo's root.
2. Import the add-on XML using the add-on importer in your Admin CP.
3. Set up the user group permissions.
4. Start adding categories and questions to your FAQ!
5. (Optional) Configure the add-on: `Home > Options > [Iversia] FAQ Manager`.

Requirements
------------
* [XenForo](http://xenforo.com/) 1.1.3

Frequently Asked Questions
------------
**I have questions added, but the [faq] BB Code says question not found.**

To reduce retrieval duplication, questions are cached before they can be used in the [faq] BB Code. The cache is updated once an hour. If you would like to force a cache update, please go to `Tools > Cron Entries` in your Admin CP and manually run the `[Iversia] Update FAQ BB Code Cache` cron.