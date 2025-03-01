# Transition effect
# TODO: Future video transitions can be fade in, fade out
from PIL import Image
import numpy as np

import cv2
def cross_dissolve(img1, img2, alpha):
    return cv2.addWeighted(img1, 1 - alpha, img2, alpha, 0)

def no_transition(img1, img2, alpha):
    # return img1 if alpha < 0.5 else img2 # This causes the final frame of a slide to jump back to the previous frame
    return img2

