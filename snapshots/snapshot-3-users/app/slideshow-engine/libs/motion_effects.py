# Clip motion effects
# Todo: Future will have panUp and panDown
# Todo: Future may have timing curve presets (ease-in, ease-out, linear) or timing curve numerical functions
from PIL import Image
import numpy as np
import cv2


# AI Assist: https://chat.openai.com/c/8c86d0f3-d5c1-49b7-94b3-8f3a53ddef6e

# AI todo: Perfect. My video's various effects of zooming and panning appears jittery. I suspect this could be from rounding down, so rather than allowing the frame positions and zooms to be continuous values, they were discrete values, causing the motion effect to jump

FRAME_RATE = 30

# Static, non-motion effect video creation.
def no_motion(img, frame_count, current_frame, use_blurred_background=True, background_color=(0, 0, 0)):
    return img

def _zoom(img, frame_count, current_frame, zoom_amount, use_blurred_background=True, background_color=(0, 0, 0)):
    height, width, _ = img.shape
    
    # Simple zoom calculation like in archived version
    zoom_per_frame = (zoom_amount - 1) / max(frame_count, 1)  # Avoid division by zero
    zoom_factor = 1 + (zoom_per_frame * current_frame)
    
    # Calculate dimensions
    if zoom_factor < 1:
        # Zooming out
        new_width = int(width * zoom_factor)
        new_height = int(height * zoom_factor)
        resized = cv2.resize(img, (new_width, new_height), interpolation=cv2.INTER_LINEAR)

        if use_blurred_background:
            blur_scale = 0.01
            blurred_background = cv2.GaussianBlur(img, (0, 0), 
                                               sigmaX=width * blur_scale, 
                                               sigmaY=height * blur_scale)
            
            overlay_x = (width - new_width) // 2
            overlay_y = (height - new_height) // 2
            
            result = blurred_background.copy()
            result[overlay_y:overlay_y + new_height, overlay_x:overlay_x + new_width] = resized
            return result
        else:
            background = np.full((height, width, 3), background_color, dtype=np.uint8)
            overlay_x = (width - new_width) // 2
            overlay_y = (height - new_height) // 2
            background[overlay_y:overlay_y + new_height, overlay_x:overlay_x + new_width] = resized
            return background
    else:
        # Zooming in
        new_width = int(width / zoom_factor)
        new_height = int(height / zoom_factor)
        center_x, center_y = width // 2, height // 2
        left_x = center_x - new_width // 2
        right_x = center_x + new_width // 2
        top_y = center_y - new_height // 2
        bottom_y = center_y + new_height // 2
        left_x, top_y = max(0, left_x), max(0, top_y)
        right_x, bottom_y = min(width, right_x), min(height, bottom_y)
        cropped = img[top_y:bottom_y, left_x:right_x]
        return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)

def pan(img, frame_count, current_frame, pan_x=0, pan_y=0, use_blurred_background=True, background_color=(0, 0, 0)):
    height, width, _ = img.shape

    # Convert pan values from relative (-1 to 1) to pixel values
    max_shift_x = int(pan_x * width)
    max_shift_y = int(pan_y * height)

    # Calculate shift per frame like in archived version
    shift_per_frame_x = max_shift_x / max(frame_count, 1)  # Avoid division by zero
    shift_per_frame_y = max_shift_y / max(frame_count, 1)
    current_shift_x = int(shift_per_frame_x * current_frame)
    current_shift_y = int(shift_per_frame_y * current_frame)

    # Calculate source and destination regions
    src_x_start = max(-current_shift_x, 0)
    src_y_start = max(-current_shift_y, 0)
    src_x_end = min(width - current_shift_x, width)
    src_y_end = min(height - current_shift_y, height)

    dest_x_start = max(current_shift_x, 0)
    dest_y_start = max(current_shift_y, 0)
    dest_x_end = min(width, dest_x_start + (src_x_end - src_x_start))
    dest_y_end = min(height, dest_y_start + (src_y_end - src_y_start))

    # Create background
    if use_blurred_background:
        blur_scale = 0.01
        background = cv2.GaussianBlur(img, (0, 0), sigmaX=width * blur_scale, sigmaY=height * blur_scale)
    else:
        background = np.full((height, width, 3), background_color, dtype=np.uint8)

    # Apply pan effect
    result = background.copy()
    cropped = img[src_y_start:src_y_end, src_x_start:src_x_end]
    result[dest_y_start:dest_y_end, dest_x_start:dest_x_end] = cropped

    return result

# actively zoom over time
def zoom(img, frame_count, current_frame, zoom_amount=1.0, use_blurred_background=True, background_color = (0, 0, 0)):
    return _zoom(img, frame_count, current_frame, zoom_amount, use_blurred_background, background_color)

# zoomed already from first frame
def zoomed(img, frame_count, current_frame, zoom_amount=1.0, use_blurred_background=True, background_color = (0, 0, 0)):
    return _zoom(img, frame_count, frame_count, zoom_amount, use_blurred_background, background_color)

def zoom_pan(img, frame_count, current_frame, zoom_amount=1.3, pan_x=0, pan_y=0, use_blurred_background=True, background_color=(0, 0, 0)):
    height, width, _ = img.shape

    # Calculate zoom and pan like in archived version
    zoom_per_frame = (zoom_amount - 1) / max(frame_count, 1)
    zoom_factor = 1 + (zoom_per_frame * current_frame)

    # Convert pan values and calculate current position
    max_shift_x = int(pan_x * width)
    max_shift_y = int(pan_y * height)
    shift_per_frame_x = max_shift_x / max(frame_count, 1)
    shift_per_frame_y = max_shift_y / max(frame_count, 1)
    current_shift_x = int(shift_per_frame_x * current_frame)
    current_shift_y = int(shift_per_frame_y * current_frame)

    if zoom_factor < 1:
        # Zooming out
        zoomed_width = int(width * zoom_factor)
        zoomed_height = int(height * zoom_factor)
        resized = cv2.resize(img, (zoomed_width, zoomed_height), interpolation=cv2.INTER_LINEAR)

        if use_blurred_background:
            blur_scale = 0.01
            blurred_background = cv2.GaussianBlur(img, (0, 0), 
                                                sigmaX=width * blur_scale, 
                                                sigmaY=height * blur_scale)
            
            overlay_x = (width - zoomed_width) // 2 + current_shift_x
            overlay_y = (height - zoomed_height) // 2 + current_shift_y
            
            result = blurred_background.copy()
            result[overlay_y:overlay_y + zoomed_height, overlay_x:overlay_x + zoomed_width] = resized
            return result
        else:
            background = np.full((height, width, 3), background_color, dtype=np.uint8)
            overlay_x = (width - zoomed_width) // 2 + current_shift_x
            overlay_y = (height - zoomed_height) // 2 + current_shift_y
            background[overlay_y:overlay_y + zoomed_height, overlay_x:overlay_x + zoomed_width] = resized
            return background
    else:
        # Zooming in
        zoomed_width = int(width / zoom_factor)
        zoomed_height = int(height / zoom_factor)

        center_x = width // 2
        center_y = height // 2

        top_left_x = max(center_x - (zoomed_width // 2) + current_shift_x, 0)
        top_left_y = max(center_y - (zoomed_height // 2) + current_shift_y, 0)

        top_left_x = min(top_left_x, width - zoomed_width)
        top_left_y = min(top_left_y, height - zoomed_height)

        cropped = img[top_left_y:top_left_y + zoomed_height, 
                     top_left_x:top_left_x + zoomed_width]
        
        return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)

# zoomed already from first frame, then zoom
def zoomed_then_zoom(img, frame_count, current_frame, zoom_amount=1.0, use_blurred_background=True, background_color = (0, 0, 0), next_zoom = 1.5):
    height, width, _ = img.shape
    
    # First apply the initial zoom by cropping and resizing
    zoomed_width = int(width / next_zoom)  # Changed from zoom_amount to next_zoom
    zoomed_height = int(height / next_zoom)  # Changed from zoom_amount to next_zoom
    
    # Calculate crop position for center
    center_x = width // 2
    center_y = height // 2
    top_left_x = center_x - (zoomed_width // 2)
    top_left_y = center_y - (zoomed_height // 2)
    
    # Ensure we don't go out of bounds
    top_left_x = max(0, min(top_left_x, width - zoomed_width))
    top_left_y = max(0, min(top_left_y, height - zoomed_height))
    
    # Crop and resize back to original dimensions
    cropped = img[top_left_y:top_left_y + zoomed_height, top_left_x:top_left_x + zoomed_width]
    resized = cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)
    
    # Then calculate the current zoom for the frame
    if frame_count > 0:
        # Calculate the interpolation factor for the current frame
        interpolation_factor = min(current_frame / frame_count, 1.0)  # Clamp to 1.0
        
        # Calculate current scale directly - swapped zoom_amount and next_zoom
        current_scale = 1.0 / (next_zoom + (zoom_amount - next_zoom) * interpolation_factor)
        current_width = int(width * current_scale)
        current_height = int(height * current_scale)
        
        if current_scale < 1:
            # For zooming out
            result = cv2.resize(resized, (current_width, current_height), interpolation=cv2.INTER_LINEAR)
            
            if use_blurred_background:
                # Create blurred background
                blur_scale = 0.01
                blurred_background = cv2.GaussianBlur(resized, (0, 0), 
                                                    sigmaX=width * blur_scale, 
                                                    sigmaY=height * blur_scale)
                
                # Calculate overlay position
                overlay_x = (width - current_width) // 2
                overlay_y = (height - current_height) // 2
                
                # Create the output image with the blurred background
                final_result = blurred_background.copy()
                # Place the resized image in the center of the blurred background
                final_result[overlay_y:overlay_y + current_height, overlay_x:overlay_x + current_width] = result
                return final_result
            else:
                # Create solid color background
                background = np.full((height, width, 3), background_color, dtype=np.uint8)
                # Calculate overlay position
                overlay_x = (width - current_width) // 2
                overlay_y = (height - current_height) // 2
                # Place the resized image in the center of the background
                background[overlay_y:overlay_y + current_height, overlay_x:overlay_x + current_width] = result
                return background
        else:
            # For zooming in
            # Calculate crop position for current zoom
            top_left_x = center_x - (current_width // 2)
            top_left_y = center_y - (current_height // 2)
            
            # Ensure we don't go out of bounds
            top_left_x = max(0, min(top_left_x, width - current_width))
            top_left_y = max(0, min(top_left_y, height - current_height))
            
            # Crop and resize for current zoom
            cropped = resized[top_left_y:top_left_y + current_height, top_left_x:top_left_x + current_width]
            return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)
    
    return resized

# zoomed already from first frame, then pan
def zoomed_then_pan(img, frame_count, current_frame, zoom_amount=1.0, use_blurred_background=True, background_color = (0, 0, 0), pan_x = 0, pan_y = 0):
    prec = _zoom(img, frame_count, frame_count, zoom_amount, use_blurred_background, background_color)
    return pan(prec, frame_count, current_frame, pan_x, pan_y, use_blurred_background=True, background_color=(0, 0, 0))

# zoomed already from first frame, then zoom and pan
def zoomed_then_zoom_pan(img, frame_count, current_frame, zoom_amount=1.0, use_blurred_background=True, background_color = (0, 0, 0), next_zoom = 1.5, pan_x = 0, pan_y = 0):
    height, width, _ = img.shape
    
    # First apply the initial zoom by cropping and resizing
    zoomed_width = int(width / next_zoom)  # Changed from zoom_amount to next_zoom
    zoomed_height = int(height / next_zoom)  # Changed from zoom_amount to next_zoom
    
    # Calculate crop position for center
    center_x = width // 2
    center_y = height // 2
    top_left_x = center_x - (zoomed_width // 2)
    top_left_y = center_y - (zoomed_height // 2)
    
    # Ensure we don't go out of bounds
    top_left_x = max(0, min(top_left_x, width - zoomed_width))
    top_left_y = max(0, min(top_left_y, height - zoomed_height))
    
    # Crop and resize back to original dimensions
    cropped = img[top_left_y:top_left_y + zoomed_height, top_left_x:top_left_x + zoomed_width]
    resized = cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)
    
    # Then calculate the current zoom and pan for the frame
    if frame_count > 0:
        # Calculate the interpolation factor for the current frame
        interpolation_factor = min(current_frame / frame_count, 1.0)  # Clamp to 1.0
        
        # Calculate current scale directly - swapped zoom_amount and next_zoom
        current_scale = 1.0 / (next_zoom + (zoom_amount - next_zoom) * interpolation_factor)
        current_width = int(width * current_scale)
        current_height = int(height * current_scale)
        
        # Calculate current pan position
        current_pan_x = int(pan_x * width * interpolation_factor)
        current_pan_y = int(pan_y * height * interpolation_factor)
        
        if current_scale < 1:
            # For zooming out
            result = cv2.resize(resized, (current_width, current_height), interpolation=cv2.INTER_LINEAR)
            
            if use_blurred_background:
                # Create blurred background
                blur_scale = 0.01
                blurred_background = cv2.GaussianBlur(resized, (0, 0), 
                                                    sigmaX=width * blur_scale, 
                                                    sigmaY=height * blur_scale)
                
                # Calculate overlay position with pan
                overlay_x = (width - current_width) // 2 + current_pan_x
                overlay_y = (height - current_height) // 2 + current_pan_y
                
                # Ensure overlay position is within bounds
                overlay_x = max(0, min(overlay_x, width - current_width))
                overlay_y = max(0, min(overlay_y, height - current_height))
                
                # Create the output image with the blurred background
                final_result = blurred_background.copy()
                # Place the resized image in the center of the blurred background
                final_result[overlay_y:overlay_y + current_height, overlay_x:overlay_x + current_width] = result
                return final_result
            else:
                # Create solid color background
                background = np.full((height, width, 3), background_color, dtype=np.uint8)
                # Calculate overlay position with pan
                overlay_x = (width - current_width) // 2 + current_pan_x
                overlay_y = (height - current_height) // 2 + current_pan_y
                
                # Ensure overlay position is within bounds
                overlay_x = max(0, min(overlay_x, width - current_width))
                overlay_y = max(0, min(overlay_y, height - current_height))
                
                # Place the resized image in the center of the background
                background[overlay_y:overlay_y + current_height, overlay_x:overlay_x + current_width] = result
                return background
        else:
            # For zooming in
            # Calculate crop position with pan
            top_left_x = center_x - (current_width // 2) + current_pan_x
            top_left_y = center_y - (current_height // 2) + current_pan_y
            
            # Ensure we don't go out of bounds
            top_left_x = max(0, min(top_left_x, width - current_width))
            top_left_y = max(0, min(top_left_y, height - current_height))
            
            # Crop and resize for current zoom
            cropped = resized[top_left_y:top_left_y + current_height, top_left_x:top_left_x + current_width]
            return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)
    
    return resized

def zoom_bar_reveal(img, frame_count, current_frame, initial_zoom=1.0, target_zoom=1.3, pan_x=0, pan_y=0, use_blurred_background=True, background_color=(0, 0, 0)):
    height, width, _ = img.shape

    # Convert pan values from relative (-1 to 1) to pixel values
    pan_x = int(pan_x * width)
    pan_y = int(pan_y * height)

    # Calculate current zoom based on linear interpolation between initial and target zoom
    progress = current_frame / max(frame_count, 1)
    current_zoom = initial_zoom + (target_zoom - initial_zoom) * progress

    # Use the "reveal" zoom calculation
    zoomed_width = int(width * (2 - current_zoom))
    zoomed_height = int(height * (2 - current_zoom))

    # Ensure zoomed dimensions don't exceed original dimensions
    zoomed_width = min(zoomed_width, width)
    zoomed_height = min(zoomed_height, height)

    # Calculate pan steps
    pan_step_x = pan_x / max(frame_count, 1)
    pan_step_y = pan_y / max(frame_count, 1)
    current_pan_x = pan_step_x * current_frame
    current_pan_y = pan_step_y * current_frame

    # Calculate crop position with pan
    center_x = width // 2
    center_y = height // 2
    top_left_x = center_x - (zoomed_width // 2) + int(current_pan_x)
    top_left_y = center_y - (zoomed_height // 2) + int(current_pan_y)

    # Ensure crop position is within bounds
    top_left_x = max(0, min(top_left_x, width - zoomed_width))
    top_left_y = max(0, min(top_left_y, height - zoomed_height))

    # Crop the image
    cropped = img[top_left_y:top_left_y + zoomed_height, 
                 top_left_x:top_left_x + zoomed_width]

    if use_blurred_background:
        # Create blurred background
        blur_scale = 0.01
        blurred_background = cv2.GaussianBlur(img, (0, 0), 
                                            sigmaX=width * blur_scale, 
                                            sigmaY=height * blur_scale)
        
        # Calculate overlay position
        overlay_x = (width - zoomed_width) // 2
        overlay_y = (height - zoomed_height) // 2
        
        # Ensure overlay position is within bounds
        overlay_x = max(0, min(overlay_x, width - zoomed_width))
        overlay_y = max(0, min(overlay_y, height - zoomed_height))
        
        # Create the output image with the blurred background
        result = blurred_background.copy()
        
        # Place the cropped image in the center of the blurred background
        result[overlay_y:overlay_y + zoomed_height, 
              overlay_x:overlay_x + zoomed_width] = cropped
        return result
    
    # If no blurred background, resize the cropped image to original dimensions
    return cv2.resize(cropped, (width, height), interpolation=cv2.INTER_LINEAR)
