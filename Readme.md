
# Boilerplate - Agile AI-Powered Full Stack SaaS App

![Last Commit](https://img.shields.io/github/last-commit/Siphon880gh/boilerplate-agile-ai-saas/main)
<a target="_blank" href="https://github.com/Siphon880gh" rel="nofollow"><img src="https://img.shields.io/badge/GitHub--blue?style=social&logo=GitHub" alt="Github" data-canonical-src="https://img.shields.io/badge/GitHub--blue?style=social&logo=GitHub" style="max-width:8.5ch;"></a>
<a target="_blank" href="https://www.linkedin.com/in/weng-fung/" rel="nofollow"><img src="https://img.shields.io/badge/LinkedIn-blue?style=flat&logo=linkedin&labelColor=blue" alt="Linked-In" data-canonical-src="https://img.shields.io/badge/LinkedIn-blue?style=flat&amp;logo=linkedin&amp;labelColor=blue" style="max-width:10ch;"></a>
<a target="_blank" href="https://www.youtube.com/@WayneTeachesCode/" rel="nofollow"><img src="https://img.shields.io/badge/Youtube-red?style=flat&logo=youtube&labelColor=red" alt="Youtube" data-canonical-src="https://img.shields.io/badge/Youtube-red?style=flat&amp;logo=youtube&amp;labelColor=red" style="max-width:10ch;"></a>

A self-contained full stack app that can deploy mixed PHP Python server for using AI to create slideshows with your documents (pictures, texts, pdf's, spreadsheets, podcasts, url's, etc). But easy enough for you to remove the spreadsheet creation which is just a placeholder for you to add your own use case.

The app is broken into multiple snapshots. The first snapshot is focused on developing parts of the apps that can be visited at meeting.html where cofounders discuss the parts of the app.

The second snapshot is when the individual demo's are combined into an app once the cofounder agrees on the features and the user flosw.

Third snapshot has user authentication, user accounts, and json web token for persistent login. A database is implemented to persist users and their slideshow creations. This allows users to make their own AI powered slideshows. There's a credit system to help with managing your subscription tiers. you can expand from here adding subscription add-ons. 

Fourth snapshot focuses on build scripts to quickly migrate the code online and to keep a persistent environment. For this purpose we are using pipenv and supervisor, rather than docker and kubernetes. A boilerplate for docker and kubernetes is coming soon for a future separate repository.

In summary, this is agile because it's streamlined iteration processes with your team using developed modular components, and then it remains agile because then you combine the demo's into an app with user flow, then integrate user authentication / user management / daabases, and finally we sprinkle consistent environment that's scalable to future user traffic growth. The placeholder boilerplate is capable of connecting to ChatGPT for rewriting user's input, so it's AI assisted. With user memberships and tiers, this is a SaaS boilerplate.


## I. Small Demo's with Team (Snapshot 1)

Visit `snapshots/snapshot-1-demos/meeting.html`

This snapshot is focused on developing parts of the apps that can be visited at meeting.html where cofounders discuss the parts of the app over a zoom or conference room projector.

You open meeting.html file and visit each part of the app as the team critiques and suggests the look and features. The features are not connected to each other yet because the best user flow hasn't been decided:
![image](Readme-assets/snapshot-1-demos.png)

### Demos

For the placeholder app, we have:
app-add-text-overlays/
app-preview-slideshow/
app-read-instructions/
app-upload-files/
app-write-prompt/

Each described here in a possible user flow:
- **app-read-instructions:** User reads a quick introduction (at read instructions) on how the app works. 

- **app-write-prompt:** On another page, the user types instructions to the AI on how to use their files to create the slideshow, if the slideshow is for schooling or business purposes, etc. You would discuss plans of implementing an AI that rewrites the instructions so that the AI slideshow creator can best understand it (Next snapshot).

- **app-upload-files and app-add-text-overlays:** User uploads various documents that the AI will combine into a slideshow. They can be pictures, podcasts, text, pdf, videos, etc. User can label their graphics which the AI can use to create more accurate slideshows (php partial app-add-text-overlays).

- **app-preview-slideshow:** Finally, user waits for the slideshow to get generated. Once it generates, they can watch the video. For demonstration purposes, you can have a placeholder video because the team is critiquing each part of the app at this phase.

The previewslideshow has a "Share" ability to email and text because you want a viralty aspect to user growth by having users share their creations. Therefore, the agile team would discuss the preview slideshow as well as the sharing ability. It obviously doesn't share a live url but a mocked demo/demo.mp4. The code for sharing which prepopulates your gmail and email add is at root app/index.js's shareController.

### Best Practice 1: Flag switch demo (modular demo's) versus live (user flow)

Each demo should have a flag to switch between demo and live mode. Live mode is when in snapshot 2, you would have the parts connected in an user flow (so that you can click a "Next" button and another part of the app opens). We'll have them all in demo mode for demonstrating to the agile team:

- **app-add-text-overlays**: This is a PHP partial for one of the other demo's, though you will be demonstrating the PHP partial by itself, therefore the flag is in PHP to impact its layout to be seen individually (without relying on any css of a parent layout):

```

    <?php
    /* config-phase */
    $pageMode = [
        'MODES' => [
            'DEMO' => 0,
            'LIVE' => 1
        ],
        'currentMode' => null
    ];

    // -> CONFIG HERE:
    $pageMode['currentMode'] = $pageMode['MODES']['DEMO'];
```

^ The flag would be switching `DEMO` or `LIVE` at `$pageMode['currentMode'] = $pageMode['MODES']['DEMO'];`

- Most other demo's will have a javascript flag, usually reassigning the submit function to either messageSelf (because this is a self contained demo) or messageParent (because Snapshot 2 where you have an user flow will actually be iframe opening iframe's on a parent page):

```
  <script class="config-phase">
      const pageMode = {
        MODES: {
          DEMO: 0,
          LIVE: 1,
        },
        currentMode: null,
      };

      // -> CONFIG HERE:
      pageMode.currentMode = pageMode.MODES.DEMO;

      function messageParent() {
        window.parent.mainController.proceedFromInstructions();
      }

      function messageSelf() {
        alert("DONE. Let's pretend we go to the next page - User will write AI prompt to design the slideshow.")
      }

      let submit = () => {}
      if (pageMode.currentMode === pageMode.MODES.LIVE) {
        submit = messageParent; // No args to pass in
      } else {
        submit = messageSelf;
      }
  </script>
```

^ The flag would be switching `DEMO` or `LIVE` at `pageMode.currentMode = pageMode.MODES.DEMO;`

### Best Practice 2: Common CSS

Each demo should link to a common css file (like common.css) that is outside the demo folder. So you can start playing with the company's brand colors and identity on the individual demo's and keep things DRY as the agile team may still be formulating the colors. Then you can adjust the css at one place for all the demo's.


### Best Practice 3: Cache Busting

Implemented a cache busting at all demo's by using a common assets/version-cache-bust.php. You could have it always cache bust with a random number or a time() or have it cache incrementally with a version number. Cache busting is necessary at this stage if you will be sharing the links to stakeholders who may not be technical enough to know how to clear their cache (when they can't see your latest changes). This cache busting and cache control can carry to the rest of the future snapshots.


---

## II. Combine Small Demo's into Full Frontend App (Snapshot 2)

Visit `snapshots/snapshot-2-app/app/` on a PHP server

Let's say the Agile cofounder team likes the modular demo's. And the team has agreed on the user flow.

It's time to create the app where the user can navigate from one page to another page. We build onto the Snapshot 1 files by adding more files.

### Menu

You may want a menu especially for certain parts of the app that can be accessed without having to go from point A to Z. This is a good point to design the menu with placeholder items like Logout, Signup, Edit Profile, Go to Dashboard, etc. The menu is also a good place for the user's eyes to see the Credits remaining. Although this is a good place to add user authentication, user accounts, credits, etc we'll focus on that on the next snapshot. Therefore this snapshot will be a full frontend app.

Furthermore for the menu, when releasing the app, you may want users to report bugs and suggest features, so the menu may be a good place for Google Form links (or design your own on the website to make it look consistent with the brand).

Some menu items may benefit from a drawer or dropdown approach that opens a secondary menu, therefore we have a dropdown.css at assets/.

### Modal windows

You may want modal window for information that needs focused or points of the app that's important. Things to appear in a modal window include login, signup, and walkthrough videos. The modal window allows the user to click out to return back to the app especially if a modal is a "pause point" (for example, user watching a walkthrough video to get the gist of the app). The walkthrough video could be accessed on and individual page (for a walkthrough on that particular feature) and/or from the menu (for a walkthrough of the entire app). Because we are using PHP, we can have a modals.php at the app root that is rendered into index.php. Any of the demo's will be able to access the modal to fill it dynamically or to simply hide/unhide it.

This boilerplate includes the menu and modal window system we just talked about at modals.php and menus/app.php. The menu named as an "app menu". You could add submenu's in the same folder and name them appropriately.

The menu item "Walkthrough" opens a modal that plays the video at assets/videos/demo-walkthrough.mp4 which is a placeholder video.

### Alpine JS Iframes

With a root index.php that has modals and all the demo's, you have several choices: 
- Can paste all the demo's codes into the app and have the demo's dynamically hide/unhide. This is too much work especially when your team may still be showing the individual demo as you're building the user flow in the app.
- Have an iframe that navigates to another page. This iframe will navigate through all the pages. This is a candidate.
- Have multiple iframes, each iframe to each demo. The iframe could be blank until it's time to render when the user reaches that point of the app, or it could be pre-rendered ahead of time. The disadvantage is slow internet connections could suffer and the complexity of the code. The advantage is that you can appear to render the page quickly (by unhiding) and you could have access to user entry from previous iframe's. Regardless of having access to previous iframes, we will still have a data model so we can have a point of truth for saving to database or displaying previous information into the DOM.

As for MVC, we have a model called `appModel` at root assets/index.js. In addition, we have a `navController` which can control behaviors of user clicking back/forth and switching to iframes (behaviors such as reloading the iframe or simply unhiding a preloaded iframe). 

As for each module, it no longer self messages but messages the parent (parent index.js whereas each module is inside an iframe). Some iframes will load when it's their turn and some may be loaded ahead of time before being visible by alpine js. Make sure to flip the switch to `LIVE`:
```
/* PHP */
// -> CONFIG HERE:
$pageMode['currentMode'] = $pageMode['MODES']['LIVE'];

/* JS in script block */
// -> CONFIG HERE:
pageMode.currentMode = pageMode.MODES.LIVE;
```

Because we use multiple iframes, we use Alpine JS that displays only one iframe depending on the "activePanel" value. That activePanel value has been enumerated for DRY and to prevent bugs - that enumeration is in assets/screens.js

You can adjust SCREENS enum from this code snippet and assets/screens.js to be semantic with your business logic. Adjust the folder or file path at iframe. Give the iframe an id if you will use js to manipulate the src. The data-will-src is optional but it refreshes the page when conditionally make it visible with alpine js. Alpine js we will change activePanel which will make their corresponding iframe visible while hiding other iframes.

```
      <!-- Panel 1 -->
      <div id="panel-1" x-show="activePanel === SCREENS.ReadInstructions" class="dynamic-panel min-h-screen min-w-screen z-40"
        data-off-class="dynamic-panel-1 bg-white p-6 border rounded-lg ">
        <iframe id="iframe-text-details" src="app-text-details/" class="dynamic-panel min-h-screen min-w-screen" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>


      <!-- Panel 2 -->
      <div id="panel-2" x-show="activePanel === SCREENS.WritePrompt" class="dynamic-panel min-h-screen min-w-screen" style="display: none;"
      >
        <iframe id="iframe-select-voice" src="about:blank" data-will-src="app-select-voice/" class="min-h-screen min-w-screen z-40" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>
```

You set which Alpine JS iframe is the first to appear on the page when user visits `app/` or `app/index.php` at this line:
```
<div id="panel-containers" x-data="{ activePanel: SCREENS.ReadInstructions }" x-init="window.activePanel = activePanel"
```

In the future, if you have to insert new pages inbetween, you can have them as IDs 10X, for example, 101 between 1 and 2, then eventually reorder them to be 1, 2 (the new one), 3 and make sure updating assets/screens.js constants to reflect that. If you’re adding pages before page 1 (like 3 additional pages), you can use -1, -2, -3

Because we now are making a full frontend app, you may want to offer different combinations of colors, fonts, button styles, etc to the team. Therefore, we created a whitelabeling system that allows switching style assets and settings depending on the "company", but for the purposes of snapshot 2 where your app is not online yet, it's experimenting with different color combinations. Looking at `assets--whitelabeler/` you'll see there are different sets of css files for default and partner1. At each demo, which we can call module this point onwards, the code refers to a brand-loader.php to determine whether to load the default or partner1. And at index.php which frames the entire app, you see in the code a brand-loader-by-url.php which looks for a ?co=default or ?co=partner1 in the url to override which brand colors to load in. There are js and php files whihc are simply variables to the name of the company, colors, logo paths, etc that are needed to complete the styling.

### Breadcrumb steps

If there is a linear user flow, as is the case with our placeholder app that creates slideshow using AI, you'd want to add breadcrumbs to the top of each page. For each module, we added this code:
```
  <?php
    $step=1;
    include("../assets/steps.php");
  ?>
```

In combination with assets/steps.php and assets/steps.css, it highlights which part of the app the user is in. You can adjust steps.php to add your pages or insert your own app:

![screenshot](Readme-assets/snapshot-2-breadcrumbs.png)

This steps snippet wouldn't be added to `app-add-text-overlays` because that's a PHP partial to be rendered inside of `app-upload-files` which does have the breadcrumb step because that's the page the user hits.

### Favico, meta tags, etc

The favico is a white square. You could work with your team to design the favico for the website (the icon that appears on browser tab, favorites, and Google search engine). Tip when designing icons and logos, you guys may want to work on only a black and white graphic, then later discuss the colors.

In a related note, you may want to adjust the meta tags at index.php. It makes sense as you're discussing how the app works as a whole, and how it looks to keep brand identity, then you may want to discuss how the app is seen on the google search, at least preliminarily. Having no meta tags or improper meta tags could harm your SEO when deploying online soon, so having something is better than nothing. Then later you can work with the marketing department to make the meta tags better.


---

## III. User Ability and Database (Snapshot 3)

Example user membership collection:
```

_id 67c2b6f7aab1143336ebfd80
userId  "67c2b6f7aab1143336ebfd7f"
appId   "APP_ABBREV"
tierLevel   0
billId  0
billStarted 0
billLate    0
billNext    0
creditsAddons Array (empty)
creditsAvailable    3
creditsTimesOne 99
creditsMonthlyResetNumber   1
createdOn   "2025-02-28 23:27:51"
trialStarted    "2025-02-28 23:27:51"
```

**TO BE CONTINUED... WIP since 3/1/25**