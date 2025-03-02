# Video Creator Script

Currently the processes to run are: Cover screen -> Sequenced -> Process audios with delays/volumes/matching duration -> Muxed -> Subtitled or Tickered or None => Creates cover-image.png, sequenced.mp4, then muxed.mp4, then subbed.mp4 or tickered.mp4 => Ultimately you want output cover-image.png, subbed.mp4/tickered.mp4/muxed.mp4

When it comes to matching audio durations: Second audio track will match first audio track's duration by looping or cropping. First audio track is the master. Second audio track is the slave.

python do_title_cover.py
    This is for making the first image the title cover with centered title.
    After finished making the new titled file, adjust do_sequence.py's first image file path
    Then if you want to add a ticker text with python ticker.py, you have to adjust start_offset if you want scrolling text to start after the title image (which is the usual practice)
python do_mux.py
python do_subtitle.py  OR python do_ticker.py

---

Currently the processes to run are:
do_title_cover:optional
do_match_audio_tracks:optional
do_delay_audio_tracks:optional
do_sequence => do_mux => do_subtitle OR do_ticker

Where sequenced images with their effects but no audio yet, you will mux the audio clips into the video (as one expects when MoviePy creates a full video).

---

zoomed:
Already zoomed at <1 or >1

zoom:
Zoom over X seconds (duration of slide) towards <1 or >1

pan:
Pan over X seconds (duration of slide) with pan X and pan Y in either direction, zero if no panning in a direction

zoom_pan:
Zoom ... towards <1 or >1... with pan X and pan Y... zero if no panning in a direction