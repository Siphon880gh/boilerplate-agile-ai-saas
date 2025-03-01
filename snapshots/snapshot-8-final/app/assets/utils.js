
var timeUtils = {
    getCurrentPSTDateTime: () => {
      // Get current UTC time
      const now = new Date();
  
      // Convert to Pacific Standard Time (PST)
      const pstOffset = -8; // PST is UTC-8
      const pstDate = new Date(now.getTime() + pstOffset * 60 * 60 * 1000);
  
      // Extract date and time components
      const year = pstDate.getUTCFullYear();
      const month = String(pstDate.getUTCMonth() + 1).padStart(2, '0'); // Months are 0-based
      const day = String(pstDate.getUTCDate()).padStart(2, '0');
      const hours = String(pstDate.getUTCHours()).padStart(2, '0');
      const minutes = String(pstDate.getUTCMinutes()).padStart(2, '0');
      const seconds = String(pstDate.getUTCSeconds()).padStart(2, '0');
  
      // Format as YYYY-MM-DD HH:MM:SS
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }
  }

  /**
 * Removes all leading slashes from a string.
 * @param {string} str - The input string.
 * @returns {string} - The string without leading slashes.
 */
function removeLeadingSlashes(str) {
  return str.replace(/^\/+/, '');
}

/**
 * Removes all trailing slashes from a string.
 * @param {string} str - The input string.
 * @returns {string} - The string without trailing slashes.
 */
function removeTrailingSlashes(str) {
  return str.replace(/\/+$/, '');
}

/**
 * Removes all slashes from a string at the start and at the end.
 * @param {string} str - The input string.
 * @returns {string} - The string without leading and trailing slashes.
 */
function removeBoundarySlashes(str) {
  return str.trim().replace(/^\/+|\/+$/g, '');
}
