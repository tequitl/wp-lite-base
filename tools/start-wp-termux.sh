#!/bin/bash

# Termux script to install PHP/wget, download wp-net-base, and run PHP server

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== WP-Net-Base Termux Setup ===${NC}"

# Check and install required packages
echo -e "${YELLOW}Checking required packages...${NC}"

# Check if wget is installed
if ! command -v wget &> /dev/null; then
    echo -e "${YELLOW}Installing wget...${NC}"
    pkg install wget -y
else
    echo -e "${GREEN}wget is already installed${NC}"
fi

# Check if php is installed
if ! command -v php &> /dev/null; then
    echo -e "${YELLOW}Installing php...${NC}"
    pkg install php -y
else
    echo -e "${GREEN}php is already installed${NC}"
fi

# Download URL
DOWNLOAD_URL="https://github.com/tequitl/wp-net-base/archive/refs/tags/v1.1.0-alpha.zip"
ZIP_FILE="wp-net-base-v1.1.0-alpha.zip"

# Check if any folder containing "wp-net-base" exists
echo -e "${YELLOW}Searching for existing wp-net-base folders...${NC}"
EXISTING_FOLDERS=($(find . -maxdepth 1 -type d -name "*wp-net-base*" 2>/dev/null))

if [ ${#EXISTING_FOLDERS[@]} -eq 0 ]; then
    echo -e "${YELLOW}No existing wp-net-base folder found. Downloading...${NC}"
    
    # Download the zip file
    if [ ! -f "$ZIP_FILE" ]; then
        echo -e "${YELLOW}Downloading wp-net-base...${NC}"
        wget "$DOWNLOAD_URL" -O "$ZIP_FILE"
        
        if [ $? -ne 0 ]; then
            echo -e "${RED}Failed to download the file${NC}"
            exit 1
        fi
    else
        echo -e "${GREEN}Zip file already exists${NC}"
    fi
    
    # Extract the zip file
    echo -e "${YELLOW}Extracting zip file...${NC}"
    unzip -o "$ZIP_FILE"
    
    # Find the extracted folder
    EXTRACTED_FOLDERS=($(find . -maxdepth 1 -type d -name "*wp-net-base*" 2>/dev/null))
    
    if [ ${#EXTRACTED_FOLDERS[@]} -eq 0 ]; then
        echo -e "${RED}Failed to find extracted folder${NC}"
        exit 1
    fi
    
    TARGET_FOLDER="${EXTRACTED_FOLDERS[0]}"
    echo -e "${GREEN}Extracted to: $TARGET_FOLDER${NC}"
    
else
    echo -e "${GREEN}Found existing wp-net-base folder(s)${NC}"
    
    if [ ${#EXISTING_FOLDERS[@]} -eq 1 ]; then
        TARGET_FOLDER="${EXISTING_FOLDERS[0]}"
        echo -e "${GREEN}Using existing folder: $TARGET_FOLDER${NC}"
    else
        echo -e "${YELLOW}Multiple folders found:${NC}"
        for i in "${!EXISTING_FOLDERS[@]}"; do
            echo "$((i+1)). ${EXISTING_FOLDERS[$i]}"
        done
        
        echo -e "${YELLOW}Enter the number of the folder you want to use:${NC}"
        read -r selection
        
        if [[ "$selection" =~ ^[0-9]+$ ]] && [ "$selection" -ge 1 ] && [ "$selection" -le ${#EXISTING_FOLDERS[@]} ]; then
            TARGET_FOLDER="${EXISTING_FOLDERS[$((selection-1))]}"
            echo -e "${GREEN}Selected folder: $TARGET_FOLDER${NC}"
        else
            echo -e "${RED}Invalid selection. Using first folder.${NC}"
            TARGET_FOLDER="${EXISTING_FOLDERS[0]}"
        fi
    fi
fi

# Start PHP server on port 80
echo -e "${YELLOW}Starting PHP server on port 80...${NC}"
echo -e "${GREEN}Server will run in: $TARGET_FOLDER${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"

cd "$TARGET_FOLDER" && php -S localhost:80