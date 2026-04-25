import React from 'react'

function App() {
  return (
    <div style={{ padding: '20px', fontFamily: 'sans-serif' }}>
      <h1>🦖 PaleoCRM Dashboard</h1>
      <p>System do zarządzania nieruchomościami (i wykopaliskami).</p>
      <div style={{ 
        marginTop: '20px', 
        padding: '15px', 
        border: '1px solid #ccc', 
        borderRadius: '8px' 
      }}>
        <h2>Status operacyjny:</h2>
        <p style={{ color: 'green' }}>✅ Claude Code zintegrowany</p>
        <p style={{ color: 'green' }}>✅ PHP 8.3 Backend gotowy</p>
      </div>
    </div>
  )
}

export default App