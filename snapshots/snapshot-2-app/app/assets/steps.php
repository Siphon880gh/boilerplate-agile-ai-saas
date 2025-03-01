<?php
$steps = [
  1 => ['<span class="fa fa-book text-xs"></span> Intro', '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path d="m19 0h-14a5.006 5.006 0 0 0 -5 5v14a5.006 5.006 0 0 0 5 5h14a5.006 5.006 0 0 0 5-5v-14a5.006 5.006 0 0 0 -5-5zm3 19a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3-3v-14a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3zm-4-10a1 1 0 0 1 -2 0 1 1 0 0 0 -1-1h-2v8h1a1 1 0 0 1 0 2h-4a1 1 0 0 1 0-2h1v-8h-2a1 1 0 0 0 -1 1 1 1 0 0 1 -2 0 3 3 0 0 1 3-3h6a3 3 0 0 1 3 3z"/></svg>'],
  2 => ['<span class="fa fa-robot text-xs"></span> AI Prompt', '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12h12V12C24,5.383,18.617,0,12,0Zm11,23H12c-6.065,0-11-4.935-11-11S5.935,1,12,1s11,4.935,11,11v11ZM6,10v3h4v.5c0,1.385-1.641,2.5-3,2.5v1c1.888,0,4-1.497,4-3.5v-5.5h-3c-1.103,0-2,.897-2,2Zm4,2h-3v-2c0-.552,.449-1,1-1h2v3Zm3-2v3h4v.5c0,1.385-1.642,2.5-3,2.5v1c1.889,0,4-1.497,4-3.5v-5.5h-3c-1.103,0-2,.897-2,2Zm4,2h-3v-2c0-.552,.448-1,1-1h2v3Z"/></svg>'],
  3 => ['<span class="fa fa-file text-xs"></span> Upload Docs', '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path d="m19.5,0H4.5C2.019,0,0,2.019,0,4.5v15c0,2.481,2.019,4.5,4.5,4.5h15c2.481,0,4.5-2.019,4.5-4.5V4.5c0-2.481-2.019-4.5-4.5-4.5Zm3.5,19.5c0,1.93-1.57,3.5-3.5,3.5H4.5c-1.93,0-3.5-1.57-3.5-3.5V4.5c0-1.93,1.57-3.5,3.5-3.5h15c1.93,0,3.5,1.57,3.5,3.5v15Zm-12-11c0,.276-.224.5-.5.5h-2.5v9.5c0,.276-.224.5-.5.5s-.5-.224-.5-.5v-9.5h-2.5c-.276,0-.5-.224-.5-.5s.224-.5.5-.5h2.5v-2.5c0-.276.224-.5.5-.5s.5.224.5.5v2.5h2.5c.276,0,.5.224.5.5Zm9,7c0,.276-.224.5-.5.5h-2.5v2.5c0,.276-.224.5-.5.5s-.5-.224-.5-.5v-2.5h-2.5c-.276,0-.5-.224-.5-.5s.224-.5.5-.5h2.5V5.5c0-.276.224-.5.5-.5s.5.224.5.5v9.5h2.5c.276,0,.5.224.5.5Z"/></svg>'],
  4 => ['<span class="fa fa-video text-xs"></span> Slideshow', '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path d="M21,1H11A3,3,0,0,0,8,4V14.026A4.948,4.948,0,0,0,5,13a5,5,0,1,0,5,5V4a1,1,0,0,1,1-1H21a1,1,0,0,1,1,1V14.026A4.948,4.948,0,0,0,19,13a5,5,0,1,0,5,5V4A3,3,0,0,0,21,1ZM5,21a3,3,0,1,1,3-3A3,3,0,0,1,5,21Zm14,0a3,3,0,1,1,3-3A3,3,0,0,1,19,21Z"/></svg>']
];

if (!isset($step)) $step = 1;
?>

<link href="../assets/steps.css" rel="stylesheet">

<div class="mt-14">
  <div class="breadcrumbs flex overflow-x-auto pb-3" style="transform: scale(1.2); width: min-content; margin: 0 auto;">
    <?php foreach($steps as $num => $stepInfo): ?>
      <?php if ($num > 1): ?>
        <div class="breadcrumb-<?=$num-1?> flex-1 flex items-center justify-center <?=$num==$step?'opacity-100':'opacity-40'?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24">
            <path d="M14 2h-7.229l7.014 7h-13.785v6h13.785l-7.014 7h7.229l10-10z"/>
          </svg>
        </div>
      <?php endif; ?>
      
      <div class="text-center px-2 <?=$num==$step?'opacity-100':'opacity-40'?>" style="min-width:150px;">
        <div class="bg-gray-300 rounded-lg flex flex-col items-center justify-center border border-gray-200 p-2">
          <h2 class="block font-normal text-gray-500 text-sm p-0 m-0"><?=$stepInfo[0]?></h2>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
