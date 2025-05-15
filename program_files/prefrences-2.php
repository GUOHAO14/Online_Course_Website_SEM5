<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Field Selection (Animated)</title>
  <link rel="stylesheet" href="prefrences-2.css">
</head>

<body>

  <div class="step-subtitle">Which field(s) are you interested in?</div>
  <div class="limit"><span class="min">Min: 1</span> <span class="max">Max: 8</span> Fields</div>

  <input type="text" class="search-bar" placeholder="Find a field">

  <div class="fields-grid-wrapper">
    <div class="fields-grid" id="fieldsGrid">
      <!-- Main fields -->
      <div class="field-card">
        
        <div class="field_name">Machine Learning</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Data Analytics</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Science</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Statistics</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Cyber Security</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Management</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card"> 
        <div class="field_name">Digital Marketing</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        <div class="field_name">Database Engineering</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
      <div class="field-card">
        
        <div class="field_name">Network Architecture</div>
        <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
      </div>
    </div>
    
    <!-- Extra fields -->
    <div class="extra-fields" id="extraFields">
      <div class="fields-grid">
        <div class="field-card">
          <div class="field_name">Frontend Development</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          
          <div class="field_name">Backend Development</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          
          <div class="field_name">Robotics</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          
          <div class="field_name">Cloud Computing</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">UX/UI Design</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Quality Assurance</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Game Development</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Bioinformatics</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Computer Vision</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Embedded Systems</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">AI Ethics</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">DevOps</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Technical Writing</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Blockchain</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
        <div class="field-card">
          <div class="field_name">Augmented Reality</div>
          <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
        </div>
      </div>
    </div>
    
  <div class="view-all" id="viewAllBtn">+ View More Fields</div>

  <div class="buttons">
    <button class="btn back" type="button">Back</button>
    <button class="btn next" type="submit" id="finishBtn">Finish</button>
  </div>

  <script>
    const viewAllBtn = document.getElementById('viewAllBtn');
    const extraFields = document.getElementById('extraFields');
    const fieldCards = document.querySelectorAll('.field-card'); // Get all field cards
    const fieldsPerPage = 9; // Number of fields to show per click
    const maxSelections = 8;
    const minSelections = 1;
    const searchInput = document.querySelector('.search-bar');
      
        // Total number of fields
    const totalFields = fieldCards.length;
    let currentPage = 0; // Track the current page of fields

    // Initially hide all extra fields
    fieldCards.forEach((field, index) => {
      if (index >= fieldsPerPage) {
        field.style.display = 'none';
      }
    });
  

      // Show the next set of fields
      viewAllBtn.addEventListener('click', () => {
      extraFields.classList.add('show');

      const allFieldCards = document.querySelectorAll('.field-card');
      const visibleFields = Array.from(allFieldCards).filter(card => card.style.display !== 'none');
      
      const start = visibleFields.length;
      const end = start + fieldsPerPage;

      let shownCount = 0;
      for (let i = start; i < allFieldCards.length && shownCount < fieldsPerPage; i++) {
        allFieldCards[i].style.display = 'flex';
        shownCount++;
      }

      setTimeout(() => {
        allFieldCards[start]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 300);

      if (document.querySelectorAll('.field-card:not([style*="display: none"])').length >= allFieldCards.length) {
        viewAllBtn.style.display = 'none';
      }
    });

      
        fieldCards.forEach(card => {
          card.addEventListener('click', () => {
            const selectedCards = document.querySelectorAll('.field-card.selected');
            if (!card.classList.contains('selected') && selectedCards.length >= maxSelections) {
              alert(`You can only select up to ${maxSelections} fields.`);
              return;
            }
            
            card.classList.toggle('selected');
          });
          
        
        });
      
        // Live search with highlight and scroll
        searchInput.addEventListener('input', () => {
      const searchValue = searchInput.value.toLowerCase();
      let firstMatch = null;

      fieldCards.forEach(card => {
        const fieldNameDiv = card.querySelector('.field_name');
        const originalText = fieldNameDiv.getAttribute('data-name') || fieldNameDiv.textContent.trim();
        fieldNameDiv.setAttribute('data-name', originalText); // Save original name if not saved yet

        if (searchValue === '') {
          card.classList.remove('hidden');
          fieldNameDiv.innerHTML = originalText; // Reset back to normal
        } else if (originalText.toLowerCase().includes(searchValue)) {
          card.classList.remove('hidden');
          if (!firstMatch) firstMatch = card;

          const regex = new RegExp(`(${searchValue})`, 'gi');
          const highlightedText = originalText.replace(regex, '<strong>$1</strong>');
          fieldNameDiv.innerHTML = highlightedText; // Only update the field_name div
        } else {
          card.classList.add('hidden');
        }
      });

      if (firstMatch) {
        firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    document.getElementById('finishBtn').addEventListener('click', () => {
      const selected = document.querySelectorAll('.field-card.selected');
      if (selected.length < minSelections) {
        alert(`Please select at least ${minSelections} field(s).`);
      } else {
        // Proceed to next step or submit form
        alert('Selections submitted successfully!');
      }
    });
  </script>
</body>
</html>
