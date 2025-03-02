// imported `finalHost` from `app-profile/profile.php` which sourced `assets/common.js`

// #region Edit Profile
/* Social media labels at the css */

/* 1:1 aspect ratio */
const ASPECT_RATIO_1_1 = "1 / 1";

/* 1.91:1 aspect ratio */
const ASPECT_RATIO_191_1 = "1.91 / 1";

/* 4:3 aspect ratio */
const ASPECT_RATIO_4_3 = "4 / 3";

/* 4:5 aspect ratio */
const ASPECT_RATIO_4_5 = "4 / 5";

/* 16:9 aspect ratio */
const ASPECT_RATIO_16_9 = "16 / 9";

/* 9:16 aspect ratio */
const ASPECT_RATIO_9_16 = "9 / 16";

/* 3:2 aspect ratio */
const ASPECT_RATIO_3_2 = "3 / 2";

/* 2:3 aspect ratio */
const ASPECT_RATIO_2_3 = "2 / 3";

/* CONFIG HERE */
const ASPECT_RATIO_STR = ASPECT_RATIO_4_5;

const ASPECT_RATIO = ((ar)=>{
    var [w, h] = ar.split("/").map(str=>str.trim());
    return w / h;
})(ASPECT_RATIO_STR);

const ProfileManager = {
    cropper: null,
    
    init() {
        // Remove DOMContentLoaded since we'll call init directly
        this.initEventListeners();
        this.onLoadGetUserData([this.initProfilePic.bind(this)]);
    },

    initEventListeners() {
        // Profile picture related
        const profilePicInput = document.getElementById('profile-pic');
        const cropBtn = document.getElementById('crop-btn');
        
        if (profilePicInput) {
            profilePicInput.addEventListener('change', this.handleFileChange.bind(this));
        } else {
            console.error('Profile pic input not found');
        }
        
        if (cropBtn) {
            cropBtn.addEventListener('click', this.cropImage.bind(this));
        } else {
            console.error('Crop button not found');
        }
        
        // Zoom controls
        document.getElementById('zoom-in')?.addEventListener('click', () => this.cropper?.zoom(0.1));
        document.getElementById('zoom-out')?.addEventListener('click', () => this.cropper?.zoom(-0.1));
        
        // Profile management
        document.getElementById('deleteProfile')?.addEventListener('click', this.handleProfileDelete.bind(this));
        document.getElementById('editProfileForm')?.addEventListener('submit', this.handleProfileUpdate.bind(this));
        document.getElementById('deletePhotoPic')?.addEventListener('click', this.deleteProfilePicture.bind(this));
        
        window.closeCropperModal = this.closeCropperModal.bind(this);
    },

    handleProfileDelete(event) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this profile?')) {
            const payload = {
                userId: window.parent.getUserId()
            };
            
            fetch(finalHost + "/profile", {
                method: "DELETE",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(resource => {
                console.log("Deleting user account:", resource);
                alert("Your account is deleted permanently! We're sorry to see you go.");
                window.parent.authController.logout();
            });
        }
    },

    handleProfileUpdate(event) {
        event.preventDefault();
        event.stopPropagation();

        const updateProfile = async (callback) => {
            const userId = window.parent.appModel.userId;
            
            if (!userId) {
                console.error("User ID not found.");
                return;
            }

            const profileData = {
                email: document.getElementById('email').value,
                full_name: document.getElementById('fullName').value,
                advanced_mode: document.getElementById('advanced-mode-checkbox').checked?1:0
            };

            console.log("Updating profile with data:", profileData);

            fetch(finalHost + `/profile/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(profileData)
            })
            .then(response => response.json())
            .then(resource => {
                console.log("Profile updated successfully:", resource);
                callback();
            })
            .catch(err => {
                console.error("Network error:", err);
                alert("Network error: Unable to update profile.");
            });
        };

        updateProfile(() => {
            alert("Profile updated successfully!");
            window.parent.location.reload();
        });
    },

    handleFileChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        event.target.value = '';
        this.setupImageCropper(file);
    },

    setupImageCropper(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const cropperArea = document.getElementById('cropper-area');
            cropperArea.innerHTML = '';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '100%';
            cropperArea.appendChild(img);

            document.getElementById('cropper-modal').style.display = 'flex';

            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }

            // Updated Cropper options
            this.cropper = new Cropper(img, {
                aspectRatio: ASPECT_RATIO,
                viewMode: 0,
                autoCropArea: 1,
                responsive: true,
                restore: true,
                background: true,
                modal: true,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                minContainerWidth: 0,
                minContainerHeight: 0,
                minCropBoxWidth: 0,
                minCropBoxHeight: 0,
                minCanvasWidth: 0,
                minCanvasHeight: 0,
                zoomOnWheel: true,
                wheelZoomRatio: 0.05,
                minZoom: 0.01,
                maxZoom: 3,
                dragMode: 'move',
                movable: true,
                scalable: true,
                zoomable: true,
                rotatable: false
            });

            // Add wheel zoom handler
            cropperArea.addEventListener('wheel', (e) => {
                e.preventDefault();
                if (this.cropper) {
                    this.cropper.zoom(e.deltaY > 0 ? -0.1 : 0.1);
                }
            });
        };
        reader.readAsDataURL(file);
    },

    cropImage() {
        if (!this.cropper) return;

        const croppedCanvas = this.cropper.getCroppedCanvas({
            fillColor: '#000000',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        const croppedImage = croppedCanvas.toDataURL('image/jpeg', {
            backgroundColor: '#000000',
            quality: 1
        });

        document.getElementById('preview-img').src = croppedImage;
        this.closeCropperModal();

        document.getElementById('profile-pic-upload').setAttribute('empty', 'false');

        croppedCanvas.toBlob((blob) => {
            const formData = new FormData();
            const fileName = `user-profile-pic-ar${ASPECT_RATIO_STR.replaceAll('/', '-').replaceAll(' ', '')}-${window.parent.getAppId()}-${window.parent.getUserId()}.jpg`;
            formData.append('profile_picture', blob, fileName);
            formData.append('userId', window.parent.getUserId());
            formData.append('appId', window.parent.getAppId());

            fetch('upload-profile-pic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    console.log('Profile picture updated:', result);
                    window.parent.appModel.profilePic = result.path;

                    var userId = window.parent.getUserId();

                    fetch(finalHost + `/profile/${userId}/profile-pic`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ profile_pic: result.path })
                    });
                } else {
                    throw new Error(result.message || 'Failed to upload profile picture');
                }
            })
            .catch(error => {
                console.error('Error uploading profile picture:', error);
                alert('Failed to upload profile picture');
            });
        }, 'image/jpeg', 1.0);
    },

    closeCropperModal() {
        const modal = document.getElementById('cropper-modal');
        modal.style.display = 'none';
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    },


    async deleteProfilePicture() {
        if (confirm('Are you sure you want to delete your profile picture?')) {
            try {
                const payload = {
                    userId: window.parent.getUserId()
                };

                const response = await fetch(finalHost + '/profile/profile-picture', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    // Reset the preview image to default
                    document.getElementById('preview-img').src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 21v-2a4 4 0 0 0-4-4H8 a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E";
                    document.getElementById('profile-pic-upload').setAttribute('empty', 'true');
                } else {
                    throw new Error('Failed to delete profile picture');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete profile picture');
            }
        }
    },

    initProfilePic() {
        const profilePic = window?.parent?.appModel?.profilePic || "";
        if (profilePic) {
            document.getElementById('profile-pic-upload').setAttribute('empty', 'false');
            document.getElementById('preview-img').src = profilePic;
        }
    },

    onLoadGetUserData(callbacks) {
        const userId = window.parent.getUserId();
        
        fetch(finalHost + `/profile/${userId}`, {
            method: "GET",
            headers: {
                'Content-Type': 'application/json'
            },
        })
        .then(response => response.json())
        .then(resource => {
            console.log(resource);
            this.populateForm(resource);
            callbacks.forEach(callback => callback());
        });
    },

    populateForm(data) {
        document.getElementById('email').value = data.email || '';
        document.getElementById('fullName').value = data.full_name || '';
        
        var isAdvancedMode = data.advanced_mode === 1;
        document.getElementById('advanced-mode-checkbox').checked = isAdvancedMode;
        window.parent.appModel.advancedMode = isAdvancedMode;
    },
};

// Initialize when the DOM is ready
document.addEventListener("DOMContentLoaded", () => ProfileManager.init());
// #endregion Edit Profile

// #region Advanced Mode
function toggleAdvancedMode(nowChecked) {
    console.log("toggleAdvancedMode");
    if(nowChecked) {
        window.parent.appModel.advancedMode = true;
    } else {
        window.parent.appModel.advancedMode = false;
    }
}
// #endregion Advanced Mode
