#[Iversia] FAQ Manager Help
A list of common issues and installation questions.

##I have questions added, but the [faq] BB Code says question not found.
	
To reduce retrieval duplication, questions are cached before they can be used in the [faq] BB Code. The cache is updated once an hour. If you would like to force a cache update, please go to `Tools > Cron Entries` in your Admin CP and manually run the `[Iversia] FAQ Update BB Code Cache` cron.

##I cannot Like FAQ answers!

1. Please ensure that you have enabled "Likes" via your FAQ Manager add-on options.
1. Please ensure that you have enabled the group permission (`Can Like FAQ answers`) for Liking answers.
1. Users are unable to Like questions/answers that they themselves have added.
1. Guests are never allowed to Like questions.


##The FAQ statistics are not updating!

FAQ Statistics are updated once a day via a cron. You may run this manually by going to `Tools > Cron Entries` in your Admin CP and running the `[Iversia] FAQ Update Statistics` cron.

##The "Slide Open" option is not working.

Please make sure that you have uploaded the included JavaScript file into your forum root (`js/faq/iversia/faq/jquery.faq.js`), and that you have JavaScript enabled in your web browser.