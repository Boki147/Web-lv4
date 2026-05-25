#!/usr/bin/env node

const { execSync } = require('child_process');
const path = require('path');

const projectDir = 'C:\\Users\\Korisnik\\Desktop\\web-lv1.worktrees\\agents-web-app-movie-management-php-mysql';

try {
    process.chdir(projectDir);
    
    // Check git log
    const log = execSync('git log --oneline -10', { encoding: 'utf-8' });
    console.log('Recent commits:');
    console.log(log);
    
    // Check status
    const status = execSync('git status --short', { encoding: 'utf-8' });
    console.log('\nGit status:');
    console.log(status);
    
    // Get diff
    const diff = execSync('git diff --cached --stat', { encoding: 'utf-8' });
    console.log('\nDiff stat:');
    console.log(diff);
    
} catch (error) {
    console.error('Error:', error.message);
}
