# Clip motion effects
# Todo: Future will have panUp and panDown
# Todo: Future may have timing curve presets (ease-in, ease-out, linear) or timing curve numerical functions
from PIL import Image
import numpy as np
import cv2


FRAME_RATE = 30
def sysPanLeft(img, frame_count, current_frame, pan_amount):
    # Calculate the width to shift for the current frame
    max_shift = min(pan_amount, img.shape[1] - 1)  # Ensure we don't exceed image width
    shift_per_frame = max_shift / frame_count
    current_shift = int(shift_per_frame * current_frame)

    # Ensure current_shift does not exceed the pan_amount
    current_shift = min(current_shift, max_shift)

    # Calculate the region of the original image to be copied
    orig_x_start = current_shift
    orig_x_end = img.shape[1]

    # Ensure we are not trying to index out of bounds
    orig_x_end = min(orig_x_end, img.shape[1])

    # Copy the visible portion of the original image to the new image
    pan_image = img[:, orig_x_start:orig_x_end]

    # If the new slice is smaller than the full frame, pad the rest with black pixels on the right
    if pan_image.shape[1] < img.shape[1]:
        padding_width = img.shape[1] - pan_image.shape[1]
        pan_image = cv2.copyMakeBorder(pan_image, 0, 0, 0, padding_width, cv2.BORDER_CONSTANT, value=[0, 0, 0])

    return pan_image

def sysPanRight(img, frame_count, current_frame, pan_amount):
    # Calculate the width to shift for the current frame
    max_shift = min(pan_amount, img.shape[1] - 1)  # Ensure we don't exceed image width
    shift_per_frame = max_shift / frame_count
    current_shift = int(shift_per_frame * current_frame)

    # Ensure current_shift does not exceed the pan_amount
    current_shift = min(current_shift, max_shift)

    # Calculate the region of the original image to be copied
    orig_x_end = img.shape[1] - current_shift
    orig_x_start = 0

    # Ensure we are not trying to index out of bounds
    orig_x_end = max(orig_x_end, 0)

    # Copy the visible portion of the original image to the new image
    pan_image = img[:, orig_x_start:orig_x_end]

    # If the new slice is smaller than the full frame, pad the rest with black pixels on the left
    if pan_image.shape[1] < img.shape[1]:
        padding_width = img.shape[1] - pan_image.shape[1]
        pan_image = cv2.copyMakeBorder(pan_image, 0, 0, padding_width, 0, cv2.BORDER_CONSTANT, value=[0, 0, 0])

    return pan_image

def sysZoom(img, frame_count, current_frame, zoom_amount):
    height, width, _ = img.shape
    zoom_per_frame = (zoom_amount - 1) / frame_count
    zoom_factor = 1 + (zoom_per_frame * current_frame)
    center_x, center_y = width // 2, height // 2
    new_width = int(width / zoom_factor)
    new_height = int(height / zoom_factor)
    left_x = max(center_x - new_width // 2, 0)
    right_x = min(center_x + new_width // 2, width)
    top_y = max(center_y - new_height // 2, 0)
    bottom_y = min(center_y + new_height // 2, height)
    cropped = img[top_y:bottom_y, left_x:right_x]
    return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)


def panLeft33(img, frame_count, current_frame, pan_x_total=100, pan_y_total=100):
    height, width, _ = img.shape

    # Calculate the amount of pixels to pan per frame
    pan_x_per_frame = pan_x_total / frame_count
    pan_y_per_frame = pan_y_total / frame_count

    # Calculate the current panning offsets
    pan_x_now = int(pan_x_per_frame * current_frame)
    pan_y_now = int(pan_y_per_frame * current_frame)

    # Create a new canvas which is the size of the original image
    canvas = np.zeros_like(img)

    # Calculate the region of the image to show on the canvas
    start_x = -pan_x_now if pan_x_now < 0 else 0
    end_x = width if pan_x_now < 0 else width - pan_x_now
    start_y = -pan_y_now if pan_y_now < 0 else 0
    end_y = height if pan_y_now < 0 else height - pan_y_now

    # Ensure we do not go out of bounds
    if end_x > width:
        end_x = width
        start_x = width - (end_x - start_x)
    if end_y > height:
        end_y = height
        start_y = height - (end_y - start_y)

    # Place the cropped part of the image onto the canvas
    canvas[start_y:start_y+end_y-start_y, start_x:start_x+end_x-start_x] = img[max(pan_y_now, 0):end_y, max(pan_x_now, 0):end_x]

    return canvas



def zoomPan(img, frame_count, current_frame, zoom_amount=1.3, pan_x=-100, pan_y=-100):
    height, width, _ = img.shape
    zoom_per_frame = (zoom_amount - 1) / frame_count
    zoom_factor = 1 + (zoom_per_frame * current_frame)

    # Calculate the size of the zoomed-in area
    zoomed_width = int(width / zoom_factor)
    zoomed_height = int(height / zoom_factor)

    # Calculate the pan step size per frame
    pan_step_x = pan_x / frame_count
    pan_step_y = pan_y / frame_count

    # Calculate the current pan position
    current_pan_x = int(pan_step_x * current_frame)
    current_pan_y = int(pan_step_y * current_frame)

    # Calculate the top-left corner of the zoomed-in area
    center_x = width // 2
    center_y = height // 2
    top_left_x = max(center_x - (zoomed_width // 2) + current_pan_x, 0)
    top_left_y = max(center_y - (zoomed_height // 2) + current_pan_y, 0)

    # Ensure the cropped area does not go out of bounds
    if top_left_x + zoomed_width > width:
        top_left_x = width - zoomed_width
    if top_left_y + zoomed_height > height:
        top_left_y = height - zoomed_height

    # Crop the zoomed-in area
    cropped = img[top_left_y:top_left_y + zoomed_height, top_left_x:top_left_x + zoomed_width]

    # Resize back to original size to maintain a full frame
    return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)

########################################################################
# These should be used at the outer level

### Actively zooming over time

def zoom110(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.1)

def zoom120(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.2)

def zoom130(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.3)

def zoom140(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.4)

def zoom150(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.5)

def zoom160(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.6)

def zoom170(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.7)

def zoom180(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.8)

def zoom190(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=1.9)

def zoom200(img, frame_count, current_frame):
    return sysZoom(img, frame_count, current_frame, zoom_amount=2.0)

### Already zoomed on the first frame of a clip/image 

def zoomed110(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.1)

def zoomed120(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.2)

def zoomed130(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.3)

def zoomed140(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.4)

def zoomed150(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.5)

def zoomed160(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.6)

def zoomed170(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.7)

def zoomed180(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.8)

def zoomed190(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=1.9)

def zoomed200(img, frame_count, current_frame):
    return sysZoom(img, frame_count, frame_count, zoom_amount=2.0)


###

def panRight33(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=33)

def panRight50(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=50)

def panRight66(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=66)

def panRight100(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=100)

def panRight100(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=100)

def panRight150(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=150)

def panRight200(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=200)

def panRight250(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=250)

def panRight300(img, frame_count, current_frame):
    return sysPanRight(img, frame_count, current_frame, pan_amount=300)

###


# def panLeft33(img, frame_count, current_frame):
#     return sysPanLeft(img, frame_count, current_frame, pan_amount=33)

def panLeft50(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=50)

def panLeft66(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=66)

def panLeft100(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=100)

def panLeft150(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=150)

def panLeft200(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=200)

def panLeft250(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=250)

def panLeft300(img, frame_count, current_frame):
    return sysPanLeft(img, frame_count, current_frame, pan_amount=300)