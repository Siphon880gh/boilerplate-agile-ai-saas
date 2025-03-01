
Boilerplate - Agile AI-Powered Full Stack SaaS App
![Last Commit](https://img.shields.io/github/last-commit/Siphon880gh/boilerplate-agile-ai-saas/main)
<a target="_blank" href="https://github.com/Siphon880gh" rel="nofollow"><img src="https://img.shields.io/badge/GitHub--blue?style=social&logo=GitHub" alt="Github" data-canonical-src="https://img.shields.io/badge/GitHub--blue?style=social&logo=GitHub" style="max-width:8.5ch;"></a>
<a target="_blank" href="https://www.linkedin.com/in/weng-fung/" rel="nofollow"><img src="https://img.shields.io/badge/LinkedIn-blue?style=flat&logo=linkedin&labelColor=blue" alt="Linked-In" data-canonical-src="https://img.shields.io/badge/LinkedIn-blue?style=flat&amp;logo=linkedin&amp;labelColor=blue" style="max-width:10ch;"></a>
<a target="_blank" href="https://www.youtube.com/@WayneTeachesCode/" rel="nofollow"><img src="https://img.shields.io/badge/Youtube-red?style=flat&logo=youtube&labelColor=red" alt="Youtube" data-canonical-src="https://img.shields.io/badge/Youtube-red?style=flat&amp;logo=youtube&amp;labelColor=red" style="max-width:10ch;"></a>

A self contained full stack app that can deploy mixed PHP Python server for using AI to create slideshows wiht your documents (pictures, texts, pdf's, spreadsheets, podcasts, url's, etc). But easy enough for you to remove the spreadsheet creation which is just a placeholder for you to add your own use case.

The app is broken into multiple snapshots. The first snapshot is focused on developing parts of the apps that can be visited at meeting.html where cofounders discuss the parts of the app.

The second snapshot is when the individual demo's are combined into an app once the cofounder agrees on the features and the user flow.

Third snapshot has user authentication, user accounts, and json web token for persistent login. This allows users to make their own AI powered slideshows. There's a credit system to help with managing your subscription tiers. you can expand from here adding subscription add-ons. 

Fourth snapshot focuses on build scripts to quickly migrate the code online and to keep a persistent environment. For this purpose we are using pipenv and supervisor, rather than docker and kubernetes. A boilerplate for docker and kubernetes is coming soon for a future separate repository.

In summary, this is agile because it's streamlined iteration processes with your team using developed modular components, and then it remains agile because then you combine the demo's into an app with user flow, then integrate user authentication / user management / daabases, and finally we sprinkle consistent environment that's scalable to future user traffic growth. The placeholder boilerplate is capable of connecting to ChatGPT for rewriting user's input, so it's AI assisted. With user memberships and tiers, this is a SaaS boilerplate.


## Setup


## Small Demo's with Team (Snapshot 1)

app-read-instructions/
app-write-prompt/
app-upload-files/
app-add-text-overlays/
app-preview-slideshow/

app-dashboard
app-profile
app-auth
app-edit-case

After renaming app.APP_ABBREV.config, grep for app.APP_ABBREV.config and adjust name in the codes that reference that file
Grep for your newly named “app.APP_ABBREV.config” or the variable `app_config_path`

Remarks about the post size for mp4 and other video files tending to be large in file sizes. The file size is set at upload-files.php and .htaccess and .www.conf (nginx but make sure to adjust server_name or integrate it to your nginx configuration).. At app-upload-files/index.php’s js sections you may want to configure on the frontend the upper limit of file upload selection allowed: `const maxFileSize = 100 * 1024 * 1024; // "A" mb in bytes`


root index.php
<div id="panel-containers" x-data="{ activePanel: SCREENS.AuthForm }" x-init="window.activePanel = activePanel"
      x-effect="window.activePanel = activePanel" class=" min-h-screen min-w-screen">
Children elements will have `x-show="activePanel === SCREENS.ReadInstructions"`


Adjust your meta tags for SEO and sharing previews - around these lines:
<meta property="og:title" content="COMPANY_NAME" />


Amend about or demonstrate: Advanced mode → advanced-only

---

## Combine Small Demo's into Full Frontend App (Snapshot 2)

Rerouted:
Edit case
Remove watermark (check if it’s watermarked obstructively) (check if users/ saved images are watermarked)

Rerouter
Iframe
Php partial for inside an iframe

Read me explaining each one
Reminds to add to sun modules and how to

---

## User Ability and Database (Snapshot 3)

Example user membership collection:
```

_id
67c2b6f7aab1143336ebfd80
userId
"67c2b6f7aab1143336ebfd7f"
appId
"APP_ABBREV"
tierLevel
0
billId
0
billStarted
0
billLate
0
billNext
0

creditsAddons
Array (empty)
creditsAvailable
3
creditsTimesOne
99
creditsMonthlyResetNumber
1
createdOn
"2025-02-28 23:27:51"
trialStarted
"2025-02-28 23:27:51"
```
